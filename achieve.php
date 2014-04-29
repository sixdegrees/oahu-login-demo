<?php 
require_once('config.php');

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // Achieve here and put a score
  if ($current_account_id) {
    try {
      $score = 1000;
      if ($_POST['score']) {
        $score = $_POST['score'];
      }
      $badge = $oahu->unlockAchievement($current_account_id, getenv('OAHU_ACHIEVEMENT_ID'), array('score' => $score));
      $result = array(201, $badge);
    } catch (Exception $e) {
      $result = array(500, array('message' => $e->getMessage()));
    }
  } else {
    // No user is logged in
    $result = array(401, false);
  }

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

  // Reset the current player. For development only !
  try {
    if ($current_account_id) {
      $result = array(204, $oahu->put("apps/" . $oahu->appId . '/players/' . $current_account_id . '/reset'));
    } else {
      $result = array(401, false);
    }    
  } catch (Exception $e) {
    $result = array(500, array('message' => $e->getMessage()));
  }
  
} else {
  $result = array(404, 'not found');
}

header('Content-Type: application/json', true, $result[0]);
echo json_encode($result[1]);

