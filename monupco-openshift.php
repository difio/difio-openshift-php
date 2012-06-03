#!/usr/bin/php

<?php

/************************************************************************************
*
* Copyright (c) 2012, Alexander Todorov <atodorov()otb.bg>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
************************************************************************************/

$NAME = "monupco-openshift-php";
$VERSION = "0.3;

# Dependencies:
#
# HTTP_Request2
# PEAR
# pecl/json

# Bugs:
# https://bugzilla.redhat.com/show_bug.cgi?id=803467#c4 - pecl/json package is not listed
# https://bugzilla.redhat.com/show_bug.cgi?id=827575 - Not able to use external PEAR channels

function startsWith($haystack,$needle) {
   return strpos($haystack, $needle, 0) === 0;
}

set_include_path(get_include_path() . PATH_SEPARATOR . getenv('OPENSHIFT_GEAR_DIR')."phplib/pear/pear/php");

require_once 'PEAR/Registry.php';
require_once 'HTTP/Request2.php';

$data = array(
    'user_id'    => intval(getenv('MONUPCO_USER_ID')),
    'app_name'   => getenv('OPENSHIFT_GEAR_NAME'),
    'app_uuid'   => getenv('OPENSHIFT_GEAR_UUID'),
    'app_type'   => getenv('OPENSHIFT_GEAR_TYPE'),
    'app_url'    => sprintf('http://%s', getenv('OPENSHIFT_GEAR_DNS')),
    'app_vendor' => 0,   // Red Hat OpenShift
    'pkg_type'   => 500, // PHP PEAR
    'installed'  => array(),
);

$registry = new PEAR_Registry(getenv('OPENSHIFT_GEAR_DIR')."phplib/pear/pear/php");
foreach ($registry->packageInfo(null, null) as $package) {
    $data['installed'][] = array('n' => $package['name'], 'v' => $package['version']['release']);
}

// Add self as installed so that user is able to see when new version is available
// this is of type 2000 - package released on GitHub which has tags
$data['installed'][] = array('n' => 'monupco/'.$NAME, 'v' => $VERSION, 't' => 2000);

$json_data = json_encode($data);

$request = new HTTP_Request2('https://monupco-otb.rhcloud.com/application/register/');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setHeader('User-agent', sprintf('%s/%s', $NAME, $VERSION));
$request->addPostParameter('json_data', $json_data);
$request->setConfig('ssl_verify_peer', false);  // another bug in OpenShift

$response = $request->send();

if (($response->getStatus() != 200) || (! startsWith($response->getHeader('Content-type'), 'application/json'))) {
    throw new Exception(sprintf('Communication failed - %s', $response->getBody()));
}

$result = json_decode($response->getBody());
printf("%s\n", $result->message);
exit($result->exit_code);

?>
