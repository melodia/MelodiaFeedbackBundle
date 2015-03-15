#MelodiaFeedbackBundle

##Installation

Step 1: Download the Bundle
---------------------------

```json
// composer.json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/melodia/MelodiaFeedbackBundle.git"
  }
]
```

```bash
$ composer require "melodia/feedback-bundle" "dev-master"
```

Step 2: Enable the Bundle
-------------------------

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Melodia\FeedbackBundle\MelodiaFeedbackBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure dependencies of the Bundle
------------------------------------------------

```yaml
# Common configuration for all Melodia API bundles

# app/config/config.yml
nelmio_api_doc: ~

jms_serializer:
    metadata:
        auto_detection: true
    property_naming:
        separator:  ~
        lower_case: false

fos_rest:
    param_fetcher_listener: force
    view:
        view_response_listener: force
        jsonp_handler: ~
    serializer:
        serialize_null: true
    routing_loader:
        default_format: json
        include_format: false
    format_listener:
      rules:
        - { path: '^/api', priorities: ['json'] }
        - { path: '^/', priorities: ['html'] }

stof_doctrine_extensions:
    orm:
        default:
            softdeleteable: true
```

Step 4: Import API router
-------------------------

```yaml
# app/config/routing.yml

melodia_page_api_:
    resource: "@MelodiaFeedbackBundle/Resources/config/routing/api.yml"
    prefix:   /api
```
