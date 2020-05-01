<div class="row ">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('live_session_cradentials'); ?>
            </h4>
         </div>
         <!-- end card body-->
      </div>
      <!-- end card -->
   </div>
   <!-- end col-->
</div>
<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            <h4 class="mb-3 header-title"><?php echo get_phrase('login_cradentials'); ?></h4>
            <strong>Institute Url:</strong>
            <p><?php echo $admin_url; ?></p>
            <strong>Instructor Url:</strong>
            <p><?php echo $admin_url; ?></p>
            <strong>Student Url:</strong>
            <p><?php echo $student_url; ?></p>
            <a href="<?php site_url('institute/courses');?>" class="btn btn-primary">Go to Courses</a>
         </div>
      </div>
   </div>
</div>