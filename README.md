Registration agent for monupco.com, preconfigured for OpenShift / PHP
applications. 

It compiles a list of installed PEAR packages and sends it to monupco.com.


Installing on your OpenShift PHP application
--------------------------------------------

- Create an account at http://monupco.com

- Create your PHP application in OpenShift

        rhc-create-app -a myapp -t php-5.3

- Add dependencies to your application:

        cd myapp/
        echo HTTP_Request2 >> deplist.txt
        echo PEAR >> deplist.txt
        echo pecl/json >> deplist.txt

- Set your userID in the `.openshift/action_hooks/pre_start_php-5.3` file

        echo "export MONUPCO_USER_ID=YourUserID"  > .openshift/action_hooks/pre_start_php-5.3

- Download the registration script into your application

        wget https://raw.github.com/monupco/monupco-openshift-php/master/monupco-openshift.php -O .openshift/action_hooks/

- Enable the registration script in `.openshift/action_hooks/post_deploy`

        #!/bin/sh
        source $OPENSHIFT_REPO_DIR/data/MONUPCO_SETTINGS
        ./monupco-openshift.php

- Commit and push your application to OpenShift

        git add . && git commit -m "enable monupco registration" && git push

- If everything goes well you should see something like:

        19:55:13 [www.0] Monupco: Success, registered/updated application with id 35

- That's it, you can now check your application statistics at <http://monupco.com>
