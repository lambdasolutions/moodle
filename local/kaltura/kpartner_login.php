<?php
require_once("../../config.php");
require_once('lib.php');

$email = optional_param('email', '', PARAM_EMAIL);
$password = optional_param('password', '', PARAM_RAW);

$PAGE->requires->js('/local/kaltura/js/kvideo.js');
$PAGE->requires->js('/local/kaltura/js/swfobject.js');

if (!empty($email))
{
  try
  {
    $kClient = new KalturaClient(KalturaHelpers::getServiceConfiguration());
    $ksId = $kClient->adminUser->login($email,$password);
    $kClient -> setKs($ksId);

    $kInfo = $kClient -> partner -> getInfo();

    $entry = new stdClass;
    $entry->plugin="local_kaltura";

    $entry->name="secret";
    $entry->value = $kInfo->secret;
    insert_record("config_plugins", $entry);

    $entry->name="adminsecret";
    $entry->value = $kInfo->adminSecret;
    insert_record("config_plugins", $entry);

    $entry->name="partner_id";
    $entry->value = $kInfo->id;
    insert_record("config_plugins", $entry);


    die('y:');
  }
  catch(Exception $exp)
  {
    die( 'n:' . $exp->getMessage());
  }
}
else
{
$PAGE->set_heading('Kaltura Partner Login');
echo $OUTPUT->header();

  echo '
  <script type="text/javascript">
  function export_settings()
  {
    var email = document.getElementById("id_email").value;
    var password = document.getElementById("id_password").value;
    document.getElementById("id_export").disabled = true;
    YUI().use("io", function(Y) {
        Y.io("'.$CFG->wwwroot.'/local/kaltura/kpartner_login.php", {
                method: "POST",
                data: "email="+email+"&password="+password,
                on: {
                    success: function(i,o,a) {
                        if (o.responseText.substr(0,2) == "y:") {
                            kalturaRefreshTop();
                            setTimeout("kalturaCloseModalBox();",0);
                        } else {
                            alert(o.responseText.substr(2));
                        }
                    },
                    failure: function(i,o,a) {
				        alert(o.responseText);
				    }
				}
		})
	})
  }
  </script>';
  $id='';
  echo '<table>
        <tr><td>' . get_string('cmsemail','local_kaltura') . '</td><td><input type="text" id="id_email" /></td></tr>'.
        '<tr><td>' . get_string('password','local_kaltura') . '</td><td><input type="password" id="id_password" /></td></tr>
        <tr><td colspan="2" style="padding-top:10px;text-align:center"><input type="button" id="id_export" onclick="export_settings();" value="'. get_string('export','local_kaltura').'"/></td></tr></table>';

	echo $OUTPUT->footer();
}
?>
