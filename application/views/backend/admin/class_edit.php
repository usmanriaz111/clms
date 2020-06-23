<?php
$class_data = $this->db->get_where('classes', array('id' => $class_id))->row_array();
$instructor_id = $this->crud_model->sync_instructor_id($class_data['course_id']);
$institute_id = $this->user_model->sync_institute_id($instructor_id);
?>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
                <a href = "<?php echo site_url('admin/class_id/'.$class_id.'/add_student'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_student'); ?></a>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">

<h4 class="header-title mb-3"><?php echo get_phrase('class_edit_form'); ?></h4>

<form action="<?php echo site_url('admin/classes/edit/' . $class_id); ?>" class="editClassForm" method="post">
<div class="form-group">
        <label for="title"><?php echo get_phrase('name'); ?><span class="required">*</span></label>
        <input class="form-control" type="text" name="name" id="name" value="<?php echo $class_data['name'] ?>" required>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('institute'); ?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="institutes" id="institutes" required>
            <?php foreach ($institutes as $institute): ?>
            <option value="<?php echo $institute['id']; ?>" <?php if ($institute['id'] == $institute_id[0]) {
    echo 'selected';
}
?>><?php echo $institute['first_name'] . ' ' . $institute['last_name']; ?></option>
            <?php endforeach;?>
            </select>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('instructor'); ?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="instructors" id="instructors" required>
            <?php foreach ($instructors as $instructor): ?>
            <option value="<?php echo $instructor['id']; ?>" <?php if ($instructor['id'] == $instructor_id[0]) {
    echo 'selected';
}
?>><?php echo $instructor['first_name'] . ' ' . $instructor['last_name']; ?></option>
            <?php endforeach;?>
            </select>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('course'); ?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="courses" id="courses" required>
            <?php foreach ($courses as $course): ?>
            <option value="<?php echo $course['id']; ?>" <?php if ($course['id'] == $class_data['course_id']) {
    echo 'selected';
}
?>><?php echo $course['title']; ?></option>
            <?php endforeach;?>
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

<script type="text/javascript">
  $(document).ready(function () {
    sync_instructor();
    $('#institutes').on('change', function(){
    sync_instructor();
    });

    $('#instructors').on('change', function(){
    sync_courses();
});
});

function sync_instructor(){
        let id = $("#institutes option:selected").val();
        $.ajax({
        url : "<?php echo base_url(); ?>admin/ajax_get_instructor",
        type : "post",
        dataType : "json",
        data : {"institute_id" : id},
        success : function(response) {
            var select = document.getElementById("instructors");
            var length = select.options.length;
            for (i = length-1; i >= 0; i--) {
            select.options[i] = null;
            }
            $.each( response, function( i, val ) {
                var newState = new Option(val.first_name+' '+val.last_name, val.id);
                $("#instructors").append(newState);
            });
            sync_courses();
        },
        error : function(response) {
            console.log(response);
        }
    });

}

function sync_courses(){
    let id = $("#instructors option:selected").val();
        $.ajax({
        url : "<?php echo base_url(); ?>admin/ajax_sync_course",
        type : "post",
        dataType : "json",
        data : {"instructor_id" : id},
        success : function(response) {
            var select = document.getElementById("courses");
            var length = select.options.length;
            for (i = length-1; i >= 0; i--) {
            select.options[i] = null;
            }
            $.each( response, function( i, val ) {
                var newState = new Option(val.title, val.id);
                $("#courses").append(newState);
            });
        },
        error : function(response) {
            console.log(response);
        }
    });
}

</script>
<script>
    $('.editClassForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            name: {
                message: 'Class name is not valid',
                validators: {
                    notEmpty: {
                        message: 'Class name is required and cannot be empty'
                    }
                }
            },
            institutes: {
                validators: {
                    notEmpty: {
                        message: 'Class institute is required and cannot be empty'
                    }
                }
            },
            instructors: {
                validators: {
                    notEmpty: {
                        message: 'Class instructor is required and cannot be empty'
                    }
                }
            },
            courses: {
                validators: {
                    notEmpty: {
                        message: 'Class course is required and cannot be empty'
                    }
                }
            }
        },
    });
</script>
