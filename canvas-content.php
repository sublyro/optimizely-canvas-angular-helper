<?php

$error_message = "";

$optimizely = $canvas->get_optimizely();
$project    = $optimizely->get_project($canvas->get_project_id());

$config                           = array();
$config['debug_flag']             = false;
$config['trigger_page_view_flag'] = true;

if ($project == null || property_exists($project, 'status')) {
    $error_message = "An error occured while loading the app! Please check the config";
} else {
    
    $app_code = $canvas->get_app_code($project);
    if ($app_code == null) {
        // no existing config. disable the app
        $canvas->set_status(0);
    } else {
        $experiments = $optimizely->get_experiments($canvas->get_project_id());
        $app_code    = $canvas->get_app_code($project);
    }
}
?>
<?php
if ($error_message != "") {
?>
<div><?php
    echo $error_message;
?></div>
<?php
} else {
?>
<?php
    if (!$canvas->is_enabled()) {
?>

<br />
<div class="attention background--warning">
  This app is currently disabled. To enable it click on the "On" button in the sidebar
</div>

<?php
    } else {
        
        if ((isset($_POST['action'])) && ($_POST['action'] == "save")) {
            $json_str = "window.optly_mvt.push([__ANGULAR_EXPERIMENTS__]);";
            
            $new_code            = readLocalFile("project_js_template.js");
            $angular_experiments = array();
            foreach ($experiments as $experiment) {
                if ($experiment->status != 'Archived') {
                    if (isset($_POST[$experiment->id])) {
                        array_push($angular_experiments, "\"" . $experiment->id . "\":true");
                        $experiment->angular = true;
                    } else {
                        $experiment->angular = false;
                    }
                }
            }
            
            //if (count($tmp_json_str) == 0) {
            $new_code = str_replace("__ANGULAR_EXPERIMENTS__", implode(",", $angular_experiments), $new_code);
            
            if (isset($_POST['debug'])) {
                $new_code             = str_replace("__ANGULAR_DEBUG__", "true", $new_code);
                $config['debug_flag'] = true;
            } else {
                $new_code             = str_replace("__ANGULAR_DEBUG__", "false", $new_code);
                $config['debug_flag'] = false;
            }
            if (isset($_POST['pageViews'])) {
                $new_code                         = str_replace("__ANGULAR_PAGE_VIEWS__", "true", $new_code);
                $config['trigger_page_view_flag'] = true;
            } else {
                $new_code                         = str_replace("__ANGULAR_PAGE_VIEWS__", "false", $new_code);
                $config['trigger_page_view_flag'] = false;
            }
            $pjs = $project->project_javascript;
            
            $canvas->replace_app_code($project, $new_code);
        } else {
            $existing_config = getExistingConfig($canvas, $project);
            if ($existing_config != null) {
                $config = $existing_config;
            }
            
            foreach ($experiments as $experiment) {
                $experiment->angular = false;
                if (isset($config['experiments']) && $experiment->status != 'Archived') {
                    foreach ($config['experiments'] as $key => $angular_experiment) {
                        if ($experiment->id == $angular_experiment) {
                            $experiment->angular = true;
                        }
                    }
                }
            } 
        }
?>

<form id="canvas-app-form" method="POST">
    <input type="hidden" name="action" id="action" value="save" />
  <input type="hidden" name="signed_request" id="signed_request" value="<?php echo $canvas->get_signed_request(); ?>"/>
    <div class="row">
      <h2>Select the experiments you want to run on Angular pages</h2>
    <ul class="experiments">
        <?php
        foreach ($experiments as $experiment) {
            if ($experiment->status != 'Archived') {
        ?>
             <li class="experiment" id="<?php echo $experiment->id; ?>">
                <input name="<?php echo $experiment->id; ?>" type="checkbox" <?php
                if ($experiment->angular == true) {
                    echo ('checked');
                } ?>/>
                <label><?php echo ($experiment->description . ' (' . $experiment->status . ')');?></label>
              </li>
          <?php
            }
        }?>
   </ul>
    </div>

    <div class="row">
    <h2>Options</h2>
    <ul>
      <li>
              <input name="pageViews" type="checkbox" value="true" <?php
        if ($config['trigger_page_view_flag'] == "true") {
            echo ('checked');
        } ?>/>
        <label>Trigger page views events (when no experiment runs)</label><br />
      </li>
      <li>
        <input name="debug" type="checkbox" value="true" <?php
        if ($config['debug_flag'] == "true") {
            echo ('checked');  } ?> /><label>Show debugging messages</label><br />
       </li>
  </div>
<div class="row">
    <button type="submit" class="button button--highlight" data-test-section="save-button"> Save </button>

</div>
</form>


<?php
    }
}


function getExistingConfig($canvas, $project) {
    $config   = array();
    $app_code = $canvas->get_app_code($project);
    if ($app_code == null) {
        // no existing config. disable the app
        $canvas->set_status(0);
    }
    if ($app_code == null)
        return null;
    preg_match("/\"debug\":([^,}]+)/i", $app_code, $res);
    if (count($res) > 0) {
        $config['debug_flag'] = $res[1];
    } else
        return null;
    
    preg_match("/\"trigger_page_view_events\":([^,}]+)/i", $app_code, $res);
    $config['trigger_page_view_flag'] = $res[1];
    
    preg_match("/.experiments = {([^}]+)/i", $app_code, $res);
    if (count($res) > 0) {
        $experiments           = str_replace("\":true", "", $res[1]);
        $experiments           = str_replace("\"", "", $experiments);
        $experiments           = explode(",", $experiments);
        $config['experiments'] = $experiments;
    } else {
        $config['experiments'] = array();
    }
    
    return $config;
}

function readLocalFile($file) {
    $res    = "";
    $handle = fopen($file, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $res .= $line;
        }
        fclose($handle);
        return $res;
    } else {
        // error opening the file.
    }
}
?>