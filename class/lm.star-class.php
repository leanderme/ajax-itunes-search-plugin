<?php
require_once(ABSPATH . '/wp-load.php');


class LM {
	var $_points = 0;
	var $_user;
	var $_momentLimit = 10;


	public function __construct()
    {
         add_action( 'init', array( $this, 'install' ) );
    }

	function install($echo = false) {
		global $table_prefix, $wpdb;

		$table_name = $table_prefix . "lm_post";
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
			$sql = "CREATE TABLE {$table_name} (
			  ID bigint(20) unsigned NOT NULL default '0',
			  votes int(10) unsigned NOT NULL default '0',
			  points int(10) unsigned NOT NULL default '0',
			  PRIMARY KEY (ID)
			);";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
			if ($echo) _e("\n");
		} else {
			if ($echo) _e("\n");
		}

		$table_name = $table_prefix . "lm_user";
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
			$sql = "CREATE TABLE {$table_name} (
			  user varchar(32) NOT NULL default '',
			  post bigint(20) unsigned NOT NULL default '0',
			  points int(10) unsigned NOT NULL default '0',
			  ip char(15) NOT NULL,
			  vote_date datetime NOT NULL,
			  PRIMARY KEY (`user`,post),
			  KEY vote_date (vote_date)
  		);";
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
			if ($echo) _e("\n");
		} elseif (!$wpdb->get_row("SHOW COLUMNS FROM {$table_name} LIKE 'vote_date'")) {
			$wpdb->query("ALTER TABLE {$table_name} ADD ip CHAR( 15 ) NOT NULL, ADD vote_date DATETIME NOT NULL");
			$wpdb->query("ALTER TABLE {$table_name} ADD INDEX (vote_date)");
			if ($echo) _e("Se ha actualizado la tabla de puntuaciones\n");
		} else {
			if ($echo) _e("\n");
		}
	}


	function getVotingStars() {
		global $id, $wpdb, $table_prefix,$current_user;
		get_currentuserinfo();
		$uid = $current_user->ID;

		
		$rated = false;
		if (isset($this->_user)) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "lm_user";
			$rated = (bool) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE user='{$user}' AND post={$id}");
		}


		if (($this->_points > 0) && !$rated) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "lm_user";
			$ip = $_SERVER['REMOTE_ADDR'];
			$vote_date = date('Y-m-d H:i:s');
			$data = $this->_getPoints();
			$vote_meta = $data->votes;
			$points_meta = $data->points;
			$wpdb->query("INSERT INTO {$table_name} (user, post, points, ip, vote_date) VALUES ('{$user}', {$id}, {$this->_points}, '{$ip}', '{$vote_date}')");
			$table_name = $table_prefix . "lm_post";
			if ($wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE ID={$id}")) {
				$wpdb->query("UPDATE {$table_name} SET votes=votes+1, points=points+{$this->_points} WHERE ID={$id};");
				
				update_post_meta($id,'rating',get_post_meta($id,'rating',true) + $this->_points);
				update_post_meta($id,'votes', $vote_meta+1);

				$rating  =  get_post_meta($id,'rating',true);
				$vote    =  get_post_meta($id,'votes',true);

				$rating_val = $rating / $vote;
				update_post_meta($id,'rating_val',$rating_val);


			} else {
				$wpdb->query("INSERT INTO {$table_name} (ID, votes, points) VALUES ({$id}, 1, {$this->_points});");
				update_post_meta($id,'rating',$this->_points);
				update_post_meta($id,'votes',$vote_meta);

				update_post_meta($id,'rating_val',$this->_points);

			}

			$rated = true;

		}
	 
		$data = $this->_getPoints();
		if ($rated || !isset($_COOKIE['wp_lm']) ||!is_user_logged_in()) {
			$html = $this->_drawStars($data->votes, $data->points);
		} else {
			$html = $this->_drawVotingStars(isset($data->votes), isset($data->points));
		}
		return $html;
	}



function getVotingStarsID($id) {
		global $wpdb, $table_prefix,$current_user;
		get_currentuserinfo();
		$uid = $current_user->ID;

		$rated = false;
		if (isset($this->_user)) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "lm_user";
			$rated = (bool) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE user='{$user}' AND post={$id}");
		}
		if (($this->_points > 0) && !$rated) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "lm_user";
			$ip = $_SERVER['REMOTE_ADDR'];
			$vote_date = date('Y-m-d H:i:s');
			$wpdb->query("INSERT INTO {$table_name} (user, post, points, ip, vote_date,uid) VALUES ('{$user}', {$id}, {$this->_points}, '{$ip}', '{$vote_date}','{$uid}')");
			$table_name = $table_prefix . "lm_post";
			if ($wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE ID={$id}")) {
				$wpdb->query("UPDATE {$table_name} SET votes=votes+1, points=points+{$this->_points} WHERE ID={$id};");
			} else {
				$wpdb->query("INSERT INTO {$table_name} (ID, votes, points) VALUES ({$id}, 1, {$this->_points});");
			}
			$rated = true;

		}
		$data = $this->_getPointsID($id);
		if ($rated || !isset($_COOKIE['wp_lm'])) {
			$html = $this->_drawStars($data->votes, $data->points);
		} else {
			$html = $this->_drawVotingStarsID($data->votes, $data->points,$id);
		}
		return $html;
	}



	function getStars() {
		$data = $this->_getPoints();
		return $this->_drawStars($data->votes, $data->points);
	}


	function _getPoints() {
		global $id, $wpdb, $table_prefix;
		$table_name = $table_prefix . "lm_post";
		return $wpdb->get_row("SELECT votes, points FROM {$table_name} WHERE ID={$id}");
	}


	function _getOnlyPoints() {
		global $id, $wpdb, $table_prefix;
		$table_name = $table_prefix . "lm_post";
		return $wpdb->get_row("SELECT points FROM {$table_name} WHERE ID={$id}");
	}

	function _getOnlyVotes() {
		global $id, $wpdb, $table_prefix;
		$table_name = $table_prefix . "lm_post";
		return $wpdb->get_row("SELECT votes FROM {$table_name} WHERE ID={$id}");
	}
	
		function _getPointsID($id) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix . "lm_post";
		return $wpdb->get_row("SELECT votes, points FROM {$table_name} WHERE ID={$id}");
	}


	function _drawStars($votes, $points) {
		if ($votes > 0) {
			$rate = $points / $votes;
		} else {
			$rate = 0;
		}?>
		<script>
			function user_notice(){
				 var opts = {
    				    title: "Oh No",
    				    text:"Please  <a href='<?php echo wp_login_url();?>' >log in </a> to vote!",
    				    type:"error",
    				    animation: 'show'
    				 }
    				$.pnotify(opts);
			}
		</script>
		<?php
		if(!is_user_logged_in()){
			$html = '<div class="LM_container"><div class="LM_stars" onclick="user_notice();"> ';
		}else{
			$html = '<div class="LM_container"><div class="LM_stars"> ';
		}
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'LM_full_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'LM_half_star';
				$char = '&frac12;';
			} else {
				$class = 'LM_no_star';
				$char = '&nbsp;';
			}
			$html .= '<span class="' . $class . '">' . $char . '</span> ';
		}
		$html .= '<p>';
		$html .= '<span class="LM_votes">' . (int) $votes . '</span> <span class="LM_tvotes">' . __('Votes') . '</span>';
		$html .= '</p>';
		$html .= '</div></div>';
		return $html;
	}

		function _drawReviewStars($points) {

		$rate = $points;

		$html = '<div class="LM_container"><div class="LM_stars"> ';
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'LM_full_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'LM_half_star';
				$char = '&frac12;';
			} else {
				$class = 'LM_no_star';
				$char = '&nbsp;';
			}
			$html .= '<span class="' . $class . '">' . $char . '</span> ';
		}
		$html .= '</div></div>';
		return $html;
	}


	function _drawVotingStars($votes, $points) {
		global $id;
		if ($votes > 0) {
			$rate = $points / $votes;
		} else {
			$rate = 0;
		}
		$html = '<div class="LM_container"><form id="LM_form_' . $id . '" action="' . $_SERVER['PHP_SELF'] . '" method="post" class="LM_stars" onmouseout="LM_star_out(this)"> ';
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'LM_full_voting_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'LM_half_voting_star';
				$char = '&frac12;';
			} else {
				$class = 'LM_no_voting_star';
				$char = '&nbsp;';
			}
			$html .= sprintf('<input type="radio" id="lm_star_%1$d_%2$d" class="star" name="lm_stars" value="%2$d" onclick="LM_save_vote(%1$d,%2$d)" /><label class="%3$s" for="lm_star_%1$d_%2$d" onmouseover="LM_star_over(this, %2$d)">%2$d</label> ', $id, $i, $class);
		}
		$html .= '<p><span class="LM_votes">' . (int) $votes . '</span> <span class="LM_tvotes">' . __('Votes') . '</p>';
		$html .= '<input type="hidden" name="p" value="' . $id . '" />';
		$html .= '<input type="submit" name="vote" value="' . __('Vote') . '" />';
		$html .= '</form></div>';
		return $html;
	}



	function _drawVotingStarsID($votes, $points,$id) {
		if ($votes > 0) {
			$rate = $points / $votes;
		} else {
			$rate = 0;
		}
		$html = '<div class="LM_container"><form id="LM_form_' . $id . '" action="' . $_SERVER['PHP_SELF'] . '" method="post" class="LM_stars" onmouseout="LM_star_out(this)"> ';
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'LM_full_voting_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'LM_half_voting_star';
				$char = '&frac12;';
			} else {
				$class = 'LM_no_voting_star';
				$char = '&nbsp;';
			}
			$html .= sprintf('<input type="radio" id="lm_star_%1$d_%2$d" class="star" name="lm_stars" value="%2$d" onclick="LM_save_vote(%1$d,%2$d)" /><label class="%3$s" for="lm_star_%1$d_%2$d" onmouseover="LM_star_over(this, %2$d)">%2$d</label> ', $id, $i, $class);
		}
		$html .= '<span class="LM_votes">' . (int) $votes . '</span> <span class="LM_tvotes">' . __('Votes');
		$html .= '<input type="hidden" name="p" value="' . $id . '" />';
		$html .= '<input type="submit" name="vote" value="' . __('Vote') . '" />';
		$html .= '</form></div>';
		return $html;
	}


	function getBestsOfMoment($limit = 10) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix . "lm_user";
		$avg = (int)$wpdb->get_var("SELECT COUNT( * ) / COUNT( DISTINCT post ) AS votes FROM {$table_name} WHERE vote_date BETWEEN DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 1 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 DAY)");
		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
			FROM {$table_name}
			WHERE vote_date BETWEEN DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 1 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 DAY)
			GROUP BY 1
			HAVING votes > {$avg}
			ORDER BY 4 DESC, 2 DESC
			LIMIT {$limit}";
		$data = $wpdb->get_results($sql);
		$oldScore = array();
		if (is_array($data)) {
			$i = 1;
			foreach ($data AS $row) {
				$oldScore[$row->post] = $i++;
			}
		}
		$avg = (int)$wpdb->get_var("SELECT COUNT( * ) / COUNT( DISTINCT post ) AS votes FROM {$table_name} WHERE vote_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()");
		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
			FROM {$table_name}
			WHERE vote_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()
			GROUP BY 1
			HAVING votes > {$avg}
			ORDER BY 4 DESC, 2 DESC
			LIMIT {$limit}";
		return $this->_drawScoreBoard($wpdb->get_results($sql), $oldScore);
	}


	function _drawScoreBoard($score, $oldScore = null) {
		if (is_array($score)) {
			$html = '<ol class="LM_moment_scores">';
			$position = 1;
			$trends = array(__('Baja'), __('Sube'), __('Se mantiene'));
			foreach ($score AS $row) {
				$html .= '<li>';
				$html .= $this->_drawStars($row->votes, $row->points);
				if (is_array($oldScore)) {
					$trend = '<span class="trend_up" title="' . $trends[1] . '">(' . $trends[1] . ')</span>';
					if (isset($oldScore[$row->post])) {
						if ($position > $oldScore[$row->post]) {
							$trend = '<span class="trend_dw" title="' . $trends[0] . '">(' . $trends[0] . ')</span>';
						} elseif ($position == $oldScore[$row->post]) {
							$trend = '<span class="trend_eq" title="' . $trends[2] . '">(' . $trends[2] . ')</span>';
						}
					}
					$html .= $trend;
				}
//				$html .= ' <span class="position">' . $row->position . '</span>';
				$title = get_the_title($row->post);
				if (strlen($title) > 32) {
					$titleAbbr = substr($title, 0, 32) . '...';
				} else {
					$titleAbbr = $title;
				}
				$html .= ' <a class="post_title" href="' . get_permalink($row->post) . '" title="' . $title . '">' . $titleAbbr . '</a> ';
				$html .= '</li>';
				$position++;
			}
			$html .= '</ol>';
			return $html;
		}
	}


	function init() {
		if (isset($_COOKIE['wp_lm'])) {
			$this->_user = $_COOKIE['wp_lm'];
		} else {
		  if (!isset($this->_user)) {
		    srand((double)microtime()*1234567);
  			$this->_user = md5(microtime() . rand(1000, 90000000));
		  }
		}
		setcookie('wp_lm', $this->_user, time()+60*60*24*365, '/');
		if (isset($_REQUEST['lm_stars'])) {
			$points = (int) $_REQUEST['lm_stars'];
			if (($points > 0) && ($points <= 5)) {
				$this->_points = $points;
			}
		}
	}



	function lm_js() {
		echo "<script type=\"text/javascript\">
		<!--
		function LM_star_over(obj, star_number) {
			var lm=obj.parentNode;
			var as=lm.getElementsByTagName('label');
			for (i=0;i<star_number;++i) {
				as[i].lastClass = as[i].className;
				as[i].className = 'LM_full_star';
			}
			for (;i<as.length;++i) {
				as[i].lastClass = as[i].className;
//				as[i].className = 'LM_no_star';
			}
		}
		function LM_star_out(obj) {
			var as=obj.getElementsByTagName('label');
			for (i=0;i<as.length;++i) {
				if (as[i].lastClass) {
					as[i].className = as[i].lastClass;
				}
			}
		}
		function LM_getHTTPObject() {
		  var xmlhttp;
		  /*@cc_on
		  @if (@_jscript_version >= 5)
		    try {
		      xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
		    } catch (e) {
		      try {
		        xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
		      } catch (E) {
		        xmlhttp = false;
		      }
		    }
		  @else
		  xmlhttp = false;
		  @end @*/
		  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
		    try {
		      xmlhttp = new XMLHttpRequest();
		    } catch (e) {
		      xmlhttp = false;
		    }
		  }
		  return xmlhttp;
		}
		function LM_save_vote(post, points) {
		  if (!LM_isWorking) {
		  	LM_current_post=post;
				LM_http.open('GET', '" . plugins_url('/lm-ajax-stars.php', dirname(__FILE__)) ."?p=' + LM_current_post + '&lm_stars=' + points, true); 
				LM_http.onreadystatechange = LM_update_vote; 
			 	LM_isWorking = true;
				LM_http.send(null);
		  }
		}
		function LM_update_vote() {
		  if (LM_http.readyState == 4) {
		  	LM_isWorking = false;
		  	var cont = document.getElementById('LM_form_' + LM_current_post).parentNode;
	    	cont.innerHTML=LM_http.responseText;
		  }
		}
		LM_current_post = null;
		LM_http=LM_getHTTPObject();
	  LM_isWorking=false;
		//-->
		</script>\n";
	}


	function wp_head($unused) {
		$this->lm_js();
	}
}
?>