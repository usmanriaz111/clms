<?php
 $class_data = $this->db->get_where('classes', array('id' => $class_id))->row_array();
 
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

                <h4 class="header-title mb-3"><?php echo get_phrase('class_edit_form'); ?></h4>

<form action="<?php echo site_url('instructor/classes/edit/'.$class_id); ?>" method="post">
    <div class="form-group">
        <label for="title"><?php echo get_phrase('name');?><span class="required">*</span></label>
        <input class="form-control" type="text" name="name" value="<?php echo $class_data['name']; ?>" id="name" required>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('select_course');?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="courses" id="courses" required>
            <?php foreach ($courses as $course): ?>
            <option value="<?php echo $course['id']; ?>" <?php if ($class_data['course_id'] == $course['id'])echo 'selected';?>><?php echo $course['title']; ?></option>
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