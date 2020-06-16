<?php
	$status_wise_courses = $this->crud_model->get_status_wise_courses();
 ?>
<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu left-side-menu-detached">
	<div class="leftbar-user">
		<a href="javascript: void(0);">
			<img src="<?php echo $this->user_model->get_user_image_url($this->session->userdata('user_id')); ?>" alt="user-image" height="42" class="rounded-circle shadow-sm">
			<?php
			$user_details = $this->user_model->get_all_user($this->session->userdata('user_id'))->row_array();
			?>
			<span class="leftbar-user-name"><?php echo $user_details['first_name'].' '.$user_details['last_name']; ?></span>
		</a>
	</div>

	<!--- Sidemenu -->
		<ul class="metismenu side-nav side-nav-light">

			<li class="side-nav-title side-nav-item"><?php echo get_phrase('navigation'); ?></li>

			<li class="side-nav-item">
				<a href="<?php echo site_url('instructor/courses'); ?>" class="side-nav-link <?php if ($page_name == 'courses' || $page_name == 'course_add' || $page_name == 'course_edit')echo 'active';?>">
					<i class="dripicons-archive"></i>
					<span><?php echo get_phrase('courses'); ?></span>
				</a>
			</li>
			<li class="side-nav-item">
				<a href="<?php echo site_url('instructor/classes'); ?>" class="side-nav-link <?php if ($page_name == 'classes' || $page_name == 'class_add' || $page_name == 'class_edit')echo 'active';?>">
					<i class="dripicons-archive"></i>
					<span><?php echo get_phrase('classes'); ?></span>
				</a>
			</li>
			<li class="side-nav-item">
				<a href="<?php echo site_url('instructor/sessions'); ?>" class="side-nav-link <?php if ($page_name == 'sessions')echo 'active';?>">
					<i class="dripicons-archive"></i>
					<span><?php echo get_phrase('live_sessions'); ?></span>
				</a>
			</li>
			<li class="side-nav-item">
				<a href="<?php echo site_url('instructor/message'); ?>" class="side-nav-link <?php if ($page_name == 'message' || $page_name == 'message_new' || $page_name == 'message_read')echo 'active';?>">
					<i class="dripicons-message"></i>
					<span><?php echo get_phrase('message'); ?></span>
				</a>
			</li>
			<!-- <li class="side-nav-item">
				<a href="<?php echo site_url('instructor/instructor_revenue'); ?>" class="side-nav-link <?php if ($page_name == 'report' || $page_name == 'invoice')echo 'active';?>">
					<i class="dripicons-media-shuffle"></i>
					<span><?php echo get_phrase('instructor_revenue'); ?></span>
				</a>
			</li> -->
	    </ul>
</div>
