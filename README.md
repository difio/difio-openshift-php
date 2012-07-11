Registration agent for Difio, preconfigured for OpenShift / PHP
applications. 

It compiles a list of installed PEAR packages and sends it to http://www.dif.io.


Installing on your OpenShift PHP application
--------------------------------------------

- Create an account at http://www.dif.io

- Create your PHP application in OpenShift

        rhc-create-app -a myapp -t php-5.3

- Add dependencies to your application:

        cd myapp/
        echo HTTP_Request2 >> deplist.txt
        echo PEAR >> deplist.txt
        echo pecl/json >> deplist.txt

- Download the registration script into your application

        wget https://raw.github.com/difio/difio-openshift-php/master/difio-openshift.php -O .openshift/action_hooks/difio-openshift.php
        chmod +x .openshift/action_hooks/difio-openshift.php

- Enable the registration script in `.openshift/action_hooks/post_deploy` and set your userID

        #!/bin/sh
        export DIFIO_USER_ID=YourUserID
        $OPENSHIFT_REPO_DIR/.openshift/action_hooks/difio-openshift.php

- Commit and push your application to OpenShift

        git add . && git commit -m "enable Difio registration" && git push

- If everything goes well you should see something like:

        remote: Running .openshift/action_hooks/post_deploy
        remote: 
        remote: Success, registered/updated application 416c208c-d212-4580-85fd-8aa207c8e2a1

- That's it, you can now check your application statistics at http://www.dif.io

Updating the registration agent
-------------------------------

- When a new version of the registration agent script is available simply overwrite your current one

        wget https://raw.github.com/difio/difio-openshift-php/master/difio-openshift.php -O .openshift/action_hooks/difio-openshift.php
        chmod +x .openshift/action_hooks/difio-openshift.php
        git add . && git commit -m "updated to latest version of difio-openshift-php" && git push
