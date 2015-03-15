<?php

/*
 * This file is part of the Melodia Feedback Bundle
 *
 * (c) Aliocha Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\FeedbackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Feedback controller
 *
 * @author Aliocha Ryzhkov <alioch@yandex.ru>
 */
class FeedbackController extends Controller
{
    public function indexAction(Request $request)
    {
        $feedbackClass = $this->container->getParameter('melodia_feedback.entity.class');
        $feedback = new $feedbackClass;

        $form = $this->createForm($this->get('melodia_feedback_form'), $feedback);
        $form->handleRequest($request);

        if ($form->isValid()) {
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

            return $this->render('MelodiaFeedbackBundle:Feedback:sent.html.twig');
        }

        return $this->render('MelodiaFeedbackBundle:Feedback:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}