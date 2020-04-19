<?php
 $plan_data = $this->db->get_where('plans', array('id' => $plan_id))->row_array();
?>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">

                <h4 class="header-title mb-3"><?php echo get_phrase('plan_edit_form'); ?></h4>

<form action="<?php echo site_url('admin/plans/edit/'.$plan_id); ?>" method="post">
    <div class="form-group">
        <label for="title"><?php echo get_phrase('no_of_courses'); ?></label>
        <input class="form-control" type="number" name="courses" value="<?php echo $plan_data['courses']; ?>" id="courses" required>
    </div>
    <div class="form-group">
        <label for="title"><?php echo get_phrase('no_minutes_per_live_session_per_course'); ?></label>
        <input class="form-control" type="text" name="course_minutes" value="<?php echo $plan_data['course_minutes']; ?>" id="course_minutes" required>
    </div>
    <div class="form-group">
        <label for="title"><?php echo get_phrase('no_of_students'); ?></label>
        <input class="form-control" type="number" name="students"  value="<?php echo $plan_data['students']; ?>" id="students" required>
    </div>
    <div class="form-group">
        <label><?php echo get_phrase('cloud_space'); ?></label>
        <input class="form-control" type="text" name="cloud_space"  value="<?php echo $plan_data['cloud_space']; ?>" id="cloud_space" required>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('select_institute'); ?></label>
         <select class="form-control select2" data-toggle="select2" name="institutes" id="institutes">
            <?php foreach ($institutes as $institute): ?>
                <option value="<?php echo $institute['id']; ?>" <?php if ($plan_data['institute_id'] == $institute['id'])echo 'selected';?>><?php echo $institute['first_name'].' '.$institute['last_name'];?></option>
            <?php endforeach; ?>
            </select>
    </div>
    <div class="text-center">
        <button class = "btn btn-success" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
    </div>
</form>
</div>
</div>
</div>
</div>