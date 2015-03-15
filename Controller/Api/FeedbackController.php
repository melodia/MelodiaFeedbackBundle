<?php

/*
 * This file is part of the Melodia Feedback Bundle
 *
 * (c) Aliocha Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\FeedbackBundle\Controller\Api;

use Engage360d\Bundle\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catalog controller
 *
 * @author Aliocha Ryzhkov <alioch@yandex.ru>
 */
class FeedbackController extends RestController
{
    /**
     * @ApiDoc(
     *  section="Feedback",
     *  description="Получение списка всех писем Обратной связи.",
     *  filters={
     *      {
     *          "name"="page",
     *          "dataType"="integer",
     *          "default"=1,
     *          "required"=false
     *      },
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "default"="inf",
     *          "required"=false
     *      }
     *  }
     * )
     */
    public function getFeedbacksAction(Request $request)
    {
        $page = $request->query->get('page') ?: 1;
        // By default this method returns all records
        $limit = $request->query->get('limit') ?: 0;

        // Check filters' format
        if (!is_numeric($page) || !is_numeric($limit)) {
            return new JsonResponse(null, 400);
        }

        $qb = $this->getDoctrine()
            ->getRepository($this->container->getParameter('melodia_feedback.entity.class'))
            ->createQueryBuilder('f')
            ->orderBy('f.sentAt', 'DESC');

        if ($page && $limit) {
            $qb
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @ApiDoc(
     *  section="Feedback",
     *  description="Получение детальной информации о письме Обратной связи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+"
     *      }
     *  }
     * )
     */
    public function getFeedbackAction($id)
    {
        $feedback = $this->getDoctrine()
            ->getRepository($this->container->getParameter('melodia_feedback.entity.class'))
            ->findOneBy(array('id' => $id));

        if (!$feedback) {
            return new JsonResponse(null, 404);
        }

        return $feedback;
    }

    /**
     *
     * @ApiDoc(
     *  section="Feedback",
     *  description="Создание и отправка письма Обратной связи.",
     *  parameters={
     *      {
     *          "name"="subject",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Basic parameter."
     *      },
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Basic parameter."
     *      },
     *      {
     *          "name"="message",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Basic parameter."
     *      },
     *      {
     *          "name"="*",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Any parameter of an extended Feedback entity."
     *      }
     *  }
     * )
     */
    public function postFeedbackAction(Request $request)
    {
        $feedbackClass = $this->container->getParameter('melodia_feedback.entity.class');
        $feedback = new $feedbackClass();

        $form = $this->createForm($this->get('melodia_feedback_form'), $feedback);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), 400);
        }

        if (!$this->container->getParameter('mailer_user')) {
            return new JsonResponse(array("mailer" => array("Mailer service is not set up. Contact with site administrator.")), 400);
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($this->container->getParameter('melodia_feedback.subject'))
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($this->container->getParameter('melodia_feedback.to_email'))
            ->setBody($this->renderView(
                'MelodiaFeedbackBundle:Feedback:message.html.twig',
                array('feedback' => $feedback)
            ))
        ;
        $this->get('mailer')->send($message);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($feedback);
        $entityManager->flush();

        return new Response($this->get('jms_serializer')->serialize($feedback, 'json'), 201);
    }

    /**
     *
     * @ApiDoc(
     *  section="Feedback",
     *  description="Изменение статуса письма Обратной связи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+"
     *      }
     *  },
     *  parameters={
     *      {
     *          "name"="isRead",
     *          "dataType"="boolean",
     *          "format"="0|1",
     *          "required"=true,
     *          "description"="The status used to determine if an email was read."
     *      }
     *  }
     * )
     */
    public function patchFeedbackAction($id)
    {
        $isRead = json_decode($this->get('request')->get('isRead'));

        if ($isRead != 0 && $isRead != 1) {
            return new JsonResponse(null, 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $feedback = $entityManager
            ->getRepository($this->container->getParameter('melodia_feedback.entity.class'))
            ->findOneBy(array('id' => $id));

        if (!$feedback) {
            return new JsonResponse(null, 404);
        }

        $feedback->setIsRead($isRead);

        $entityManager->persist($feedback);
        $entityManager->flush();

        return $feedback;
    }

    /**
     * @ApiDoc(
     *  section="Feedback",
     *  description="Удаление письма Обратной связи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+"
     *      }
     *  }
     * )
     */
    public function deleteFeedbackAction($id)
    {
        $feedback = $this->getDoctrine()
            ->getRepository($this->container->getParameter('melodia_feedback.entity.class'))
            ->findOneBy(array('id' => $id));

        if (!$feedback) {
            return new JsonResponse(null, 404);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($feedback);
        $entityManager->flush();

        return new JsonResponse(null, 200);
    }
}
