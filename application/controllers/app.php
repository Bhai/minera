<?php if (!defined('BASEPATH')) die();
class App extends Main_Controller {

	public function index()
	{	
		$data['htmlTag'] = "lockscreen";
		$this->load->view('include/header', $data);
		$this->load->view('lockscreen');
	}
	
	public function login()
	{
		if ($this->input->post('password', true) && $this->input->post('password', true) == $this->redis->get('minera_password'))
		{
			$this->session->set_userdata("loggedin", 1);
			redirect('app/dashboard');
		}
		else
			redirect('app/index');
	}
	
	/*
	// Shutdown the system
	*/
	public function shutdown()
	{	
		if ($this->input->get('confirm'))
		{
			$data['message'] = "Please wait to unplug me.";
			$data['timer'] = true;
			$this->util_model->shutdown();
		}
		else
		{
			$data['title'] = "Are you sure?";
			$data['message'] = '<a href="'.site_url("app/shutdown").'?confirm=1"><button class="btn btn-default btn-lg"><i class="fa fa-check"></i> Yes, shutdown now</button></a>&nbsp;&nbsp;&nbsp;<a href="'.site_url("app/dashboard").'"><button class="btn btn-default btn-lg"><i class="fa fa-times"></i> No, thanks</button></a>';
			$data['timer'] = false;
		}
		$data['messageEnd'] = "you can unplug me now.";
		$data['htmlTag'] = "lockscreen";
		$data['seconds'] = 60;
		$data['refreshUrl'] = false;
		$this->load->view('include/header', $data);
		$this->load->view('sysop', $data);
	}

	/*
	// Reboot the system
	*/
	public function reboot()
	{	
		if ($this->input->get('confirm'))
		{
			$data['message'] = "Please wait while I'm rebooting...";
			$data['timer'] = true;
			$this->util_model->reboot();
		}
		else
		{
			$data['title'] = "Are you sure?";
			$data['message'] = '<a href="'.site_url("app/reboot").'?confirm=1"><button class="btn btn-default btn-lg"><i class="fa fa-check"></i> Yes, reboot now</button></a>&nbsp;&nbsp;&nbsp;<a href="'.site_url("app/dashboard").'"><button class="btn btn-default btn-lg"><i class="fa fa-times"></i> No, thanks</button></a>';
			$data['timer'] = false;
		}
		$data['messageEnd'] = "here we go!";
		$data['htmlTag'] = "lockscreen";
		$data['seconds'] = 30;
		$data['refreshUrl'] = site_url("app/index");
		$this->load->view('include/header', $data);
		$this->load->view('sysop', $data);
	}

	/*
	// Start the miner
	*/
	public function start_miner()
	{
		if (!$this->session->userdata("loggedin"))
			redirect('app/index');
		
		$this->util_model->minerdStart();
		
		redirect('app/dashboard');
	}

	/*
	// Stop the miner
	*/
	public function stop_miner()
	{
		if (!$this->session->userdata("loggedin"))
			redirect('app/index');
		
		$this->util_model->minerdStop();
		
		redirect('app/dashboard');
	}
			
	/*
	// Dashboard controller
	*/	
	public function dashboard()
	{
		if (!$this->session->userdata("loggedin"))
			redirect('app/index');
		
		$data['btc'] = $this->util_model->getBtcValue();
		$data['isOnline'] = $this->util_model->isOnline();
		$data['htmlTag'] = "dashboard";
		
		$this->load->view('include/header', $data);
		$this->load->view('include/sidebar', $data);
		$this->load->view('frontpage', $data);
		$this->load->view('include/footer');
	}

	/*
	// Settings controller
	*/
	public function settings()
	{
		if (!$this->session->userdata("loggedin"))
			redirect('app/index');

		if ($this->input->post('save_settings'))
		{
			$settings = trim($this->input->post('minerd_settings'));
			if (!empty($settings))
			{
				$this->redis->set("minerd_settings", $settings);
				$this->util_model->saveStartupScript();
				
				$data['message'] = '<b>Success!</b> Settings saved!';
				$data['message_type'] = "success";
			}
			else
			{
				$data['message'] = "<b>Warning!</b> Minerd options can't be empty";
				$data['message_type'] = "warning";
			}
		}

		if ($this->input->post('save_password'))
		{
			$password = trim($this->input->post('password'));
			$password2 = trim($this->input->post('password2'));
			if (empty($password) && empty($password2))
			{
				$data['message'] = "<b>Warning!</b> Password can't be empty";
				$data['message_type'] = "warning";
			}
			elseif ($password != $password2)
			{
				$data['message'] = "<b>Warning!</b> Password mismatch";
				$data['message_type'] = "warning";				
			}
			else
			{
				$this->redis->set("minera_password", $password);
				$data['message'] = '<b>Success!</b> Password saved!';
				$data['message_type'] = "success";
			}
		}
		
		$data['btc'] = $this->util_model->getBtcValue();
		$data['isOnline'] = $this->util_model->isOnline();
		$data['htmlTag'] = "settings";
		
		$this->load->view('include/header', $data);
		$this->load->view('include/sidebar', $data);
		$this->load->view('settings', $data);
		$this->load->view('include/footer');
	}
		
	public function stats()
	{
		$stats = $this->util_model->getStats();
		
		$this->output
			->set_content_type('application/json')
			->set_output($stats);
	}
	
	public function stored_stats()
	{
		$storedStats = $this->util_model->getStoredStats();
		
		$this->output
			->set_content_type('application/json')
			->set_output("[".implode(",", $storedStats)."]");
	}	

	public function cron_stats()
	{
		$this->util_model->storeStats();
	}
}

/* End of file frontpage.php */
/* Location: ./application/controllers/frontpage.php */
