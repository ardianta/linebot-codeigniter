<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tebakkode_m extends CI_Model {

  function __construct(){
    parent::__construct();
    $this->load->database();
  }

  function log_events($signature, $body)
  {
    $this->db->set('signature', $signature)
    ->set('events', $body)
    ->insert('eventlog');

    return $this->db->insert_id();

    // tampilkan error logs
    error_log($body);
  }

  function saveUser($profile)
  {

    $this->db->set('user_id', $profile['userId'])
    ->set('display_name', $profile['displayName'])
    ->insert('users');

    return $this->db->insert_id();

    }


}
