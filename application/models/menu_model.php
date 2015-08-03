<?php
if ( !defined( 'BASEPATH' ) )
	exit( 'No direct script access allowed' );
class Menu_model extends CI_Model
{
	public function create($name,$description,$keyword,$url,$linktype,$parentmenu,$menuaccess,$isactive,$order,$icon)
	{ 
		date_default_timezone_set('Asia/Calcutta');
		$data  = array(
			'description' =>$description,
			'name' => $name,
			'keyword' => $keyword,
			'url' => $url,
			'linktype' => $linktype,
			'parent' => $parentmenu,
			'isactive' => $isactive,
			'order' => $order,
			'icon' => $icon,
		);
		//print_r($data);
		
		$query=$this->db->insert( 'menu', $data );
		$menuid=$this->db->insert_id();
		if(! empty($menuaccess)) {
			foreach($menuaccess as $row)
			{
				$data  = array(
					'menu' => $menuid,
					'access' => $row,
				);
				$query=$this->db->insert( 'menuaccess', $data );
			}
		}
		if(!$query)
			return  0;
		else
			return  1;
	}
	function viewmenu()
	{
		$query="SELECT `menu`.`id` as `id`,`menu`.`name` as `name`,`menu`.`description` as `description`,`menu`.`keyword` as `keyword`,`menu`.`url` as `url`,`menu2`.`name` as `parentmenu`,`menu`.`linktype` as `linktype`,`menu`.`icon`,`menu`.`order` FROM `menu`
		LEFT JOIN `menu` as `menu2` ON `menu2`.`id` = `menu`.`parent` 
		ORDER BY `menu`.`order` ASC";
	   
		$query=$this->db->query($query)->result();
		return $query;
	}
	public function beforeedit( $id )
	{
		$this->db->where( 'id', $id );
		$query['menu']=$this->db->get( 'menu' )->row();
		$query['menuaccess']=array();
		$menu_arr=$this->db->query("SELECT `access` FROM `menuaccess` WHERE `menu`='$id' ")->result();
		foreach($menu_arr as $row)
		{
			$query['menuaccess'][]=$row->access;
	    }
		
		return $query;
	}
	
	public function edit($id,$name,$description,$keyword,$url,$linktype,$parentmenu,$menuaccess,$isactive,$order,$icon)
	{
		$data  = array(
			'description' =>$description,
			'name' => $name,
			'keyword' => $keyword,
			'url' => $url,
			'linktype' => $linktype,
			'parent' => $parentmenu,
			'isactive' => $isactive,
			'order' => $order,
			'icon' => $icon,
		);
		$this->db->where( 'id', $id );
		$this->db->update( 'menu', $data );
		
		$this->db->query("DELETE FROM `menuaccess` WHERE `menu`='$id'");
		if(! empty($menuaccess)) {
		foreach($menuaccess as  $row)
		{
			$data  = array(
				'menu' => $id,
				'access' => $row,
			);
			$query=$this->db->insert( 'menuaccess', $data );
			
		} }
		return 1;
	}
	function deletemenu($id)
	{
		$query=$this->db->query("DELETE FROM `menu` WHERE `id`='$id'");
		$query=$this->db->query("DELETE FROM `menuaccess` WHERE `menu`='$id'");
	}
	public function getmenu()
	{
		$query=$this->db->query("SELECT * FROM `menu`  ORDER BY `id` ASC" )->result();
		$return=array(
		"" => ""
		);
		
		foreach($query as $row)
		{
			$return[$row->id]=$row->name;
		}
		return $return;
	}
	function viewmenus()
	{
        $accesslevel=$this->session->userdata( 'accesslevel' );
		$query="SELECT `menu`.`id` as `id`,`menu`.`name` as `name`,`menu`.`description` as `description`,`menu`.`keyword` as `keyword`,`menu`.`url` as `url`,`menu2`.`name` as `parentmenu`,`menu`.`linktype` as `linktype`,`menu`.`icon` FROM `menu`
		LEFT JOIN `menu` as `menu2` ON `menu2`.`id` = `menu`.`parent`  
        INNER  JOIN `menuaccess` ON  `menuaccess`.`menu`=`menu`.`id`
		WHERE `menu`.`parent`=0 AND `menuaccess`.`access`='$accesslevel'
		ORDER BY `menu`.`order` ASC";
	   
		$query=$this->db->query($query)->result();
		return $query;
	}
	function getsubmenus($parent)
	{
		$query="SELECT `menu`.`id` as `id`,`menu`.`name` as `name`,`menu`.`description` as `description`,`menu`.`keyword` as `keyword`,`menu`.`url` as `url`,`menu`.`linktype` as `linktype`,`menu`.`icon` FROM `menu`
		WHERE `menu`.`parent` = '$parent'
		ORDER BY `menu`.`order` ASC";
	   
		$query=$this->db->query($query)->result();
		return $query;
	}
	function getpages($parent)
	{ 
		$query="SELECT `menu`.`id` as `id`,`menu`.`name` as `name`,`menu`.`url` as `url` FROM `menu`
		WHERE `menu`.`parent` = '$parent'
		ORDER BY `menu`.`order` ASC";
	   
		$query2=$this->db->query($query)->result();
		$url = array();
		foreach($query2 as $row)
		{
			$pieces = explode("/", $row->url);
					
			if(empty($pieces) || !isset($pieces[1]))
			{
				$page2="";
			}
			else
				$page2=$pieces[1];
				
			$url[]=$page2;
		}
		//print_r($url);
		return $url;
	}
    
    function sendquestiontousers($questionid)
    {
        $allusers=$this->db->query("SELECT * FROM `user` WHERE `accesslevel`=4")->result();
        foreach($allusers as $user)
        {
            $userid=$user->id;
            $email=$user->email;
            $hashvalue=base64_encode ($userid."&hq");
            $link="<a href='http://www.localhost/hq/index.php/user/ghghghh/$hashvalue'>Click here </a>";
               
            $this->load->library('email');
            $this->email->from('dhavalwohlig@gmail.com', 'HQ');
            $this->email->to($email);
            $this->email->subject('HQ');   

            $message = "<html>

                            <body>
                                <div style='text-align:center;   width: 50%; margin: 0 auto;'>
                                    <h4 style='font-size:1.5em;padding-bottom: 5px;color: #e82a96;'>HQ</h4>
                                    <p style='font-size: 1em;padding-bottom: 10px;'>Click Link To Answer:</p>
                                    <p style='font-size: 1em;padding-bottom: 10px;'>$link</p>
                                </div>
                                <div style='text-align:center;position: relative;'>
                                    <p style=' position: absolute; top: 8%;left: 50%; transform: translatex(-50%); font-size: 1em;margin: 0; letter-spacing:2px; font-weight: bold;'>
                                        Thank You
                                    </p>
                                </div>
                            </body>

                        </html>";
            $this->email->message($message);
            $this->email->send();
        }
    }
    
    function getweightofpillarsbyuser($userid)
    {
        $query=$this->db->query("SELECT * FROM `hq_pillar` ORDER BY `order` ASC")->result();
        foreach($query as $row)
        {
			$pillarid = $row->id;
			$pillaraveragebyuserid=$this->db->query("SELECT IFNULL(SUM(`hq_options`.`weight`),0) AS `totalweight`
FROM `hq_useranswer`  LEFT OUTER JOIN `hq_options` ON `hq_options`.`id`=`hq_useranswer`.`option`
			WHERE `hq_useranswer`.`pillar`='$pillarid' AND `hq_useranswer`.`user`='$userid'")->row();
            $row->pillaraveragebyuserid=$pillaraveragebyuserid->totalweight;
        }
        
        $teamquery=$this->db->query("SELECT * FROM `user` WHERE `id`='$userid'")->row();
        $teamid=$teamquery->team;
        $allteamusers=$this->db->query("SELECT * FROM `user` WHERE `team`='$teamid'")->result();
        $totalusersinteam=count($allteamusers);
        
        $alluseridsofteam="(";
        foreach($allteamusers as $key=>$value)
        {
            if($key==0)
            {
                $alluseridsofteam.=$value->id;
            }
            else
            {
                $alluseridsofteam.=",".$value->id;
            }
        }
        $alluseridsofteam=")";
        
        foreach($query as $row)
        {
			$pillarid = $row->id;
			$pillaraveragebyteam=$this->db->query("SELECT IFNULL(SUM(`hq_options`.`weight`),0) AS `totalweight`
FROM `hq_useranswer`  LEFT OUTER JOIN `hq_options` ON `hq_options`.`id`=`hq_useranswer`.`option`  LEFT OUTER JOIN `user` ON `user`.`id`=`hq_useranswer`.`user`
			WHERE `hq_useranswer`.`pillar`='$pillarid' AND `hq_useranswer`.`user` IN $alluseridsofteam ")->row();
            $totalweight=($pillaraveragebyteam->totalweight)/$totalusersinteam;
            $row->pillaraveragebyteam=$totalweight;
        }
        
        
        $allorganizationusers=$this->db->query("SELECT * FROM `user` WHERE `accesslevel`=4")->result();
        $totalusersinorganization=count($allorganizationusers);
        
        $alluseridsoforganization="(";
        foreach($allorganizationusers as $key=>$value)
        {
            if($key==0)
            {
                $alluseridsoforganization.=$value->id;
            }
            else
            {
                $alluseridsoforganization.=",".$value->id;
            }
        }
        $alluseridsoforganization=")";
        
        
        foreach($query as $row)
        {
			$pillarid = $row->id;
			$pillaraveragebyorganization=$this->db->query("SELECT IFNULL(SUM(`hq_options`.`weight`),0) AS `totalweight`
FROM `hq_useranswer`  LEFT OUTER JOIN `hq_options` ON `hq_options`.`id`=`hq_useranswer`.`option`  LEFT OUTER JOIN `user` ON `user`.`id`=`hq_useranswer`.`user`
			WHERE `hq_useranswer`.`pillar`='$pillarid'")->row();
            $totalweight=($pillaraveragebyorganization->totalweight)/$totalusersinorganization;
            $row->pillaraveragebyorganization=$totalweight;
        }
        return $query;
    }
    
//    function getweightofpillarsbyteam($userid)
//    {
//        $teamquery=$this->db->query("SELECT * FROM `user` WHERE `id`='$userid'")->row();
//        $teamid=$teamquery->team;
//        $query=$this->db->query("SELECT * FROM `hq_pillar` ORDER BY `order` ASC")->result();
//        foreach($query as $row)
//        {
//			$pillarid = $row->id;
//			$pillaraveragebyuserid=$this->db->query("SELECT IFNULL(SUM(`hq_options`.`weight`),0) AS `totalweight`
//FROM `hq_useranswer`  LEFT OUTER JOIN `hq_options` ON `hq_options`.`id`=`hq_useranswer`.`option`  LEFT OUTER JOIN `user` ON `user`.`id`=`hq_useranswer`.`user`
//			WHERE `hq_useranswer`.`pillar`='$pillarid' AND `user`.`team`='$teamid'")->row();
//            $row->pillaraveragebyteam=$pillaraveragebyteam->totalweight;
//        }
//        return $query;
//    }
}
?>