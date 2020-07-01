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

                <h4 class="header-title mb-3"><?php echo get_phrase('plan_add_form'); ?></h4>

<form action="<?php echo site_url('admin/plans/'.$param1.'add'); ?>" method="post" class="planForm">
    <div class="form-group">
        <label for="name"><?php echo get_phrase('plane_name'); ?></label>
        <input class="form-control" type="text" name="name" id="name" required>
    </div>
    <div class="form-group">
        <label for="price"><?php echo get_phrase('plane_price'); ?></label>
        <input class="form-control" min="0" type="text" name="price" id="price" required>
    </div>
    <div class="form-group">
        <label for="courses"><?php echo get_phrase('no_of_courses'); ?></label>
        <input class="form-control" min="1" type="number" name="courses" id="courses" required>
    </div>
    <div class="form-group">
        <label for="classes"><?php echo get_phrase('no_of_classes_per_course'); ?></label>
        <input class="form-control" min="1" type="number" name="classes" id="classes" required>
    </div>
    <div class="form-group">
        <label for="course_minutes"><?php echo get_phrase('no_of_minutes_per_live_session_per_class'); ?></label>
        <input class="form-control" type="number" min="1" name="course_minutes" id="course_minutes" required>
    </div>
    <div class="form-group">
        <label for="students"><?php echo get_phrase('no_of_students'); ?></label>
        <input class="form-control" min="1" type="number" name="students" id="students" required>
    </div>
    <div class="form-group">
        <label for="cloud_space"><?php echo get_phrase('cloud_space'); ?></label>
        <input class="form-control" min="1" type="number" name="cloud_space" id="cloud_space" required>
    </div>
    <div class="form-group">
        <label for="cloud_space"><?php echo get_phrase('plan_type'); ?></label>
        <select name="plan_type" class="form-control" required>
        <option value="paid">Paid</option>
        <option value="free">Free</option>
        </select>
    </div>
    <div class="form-group">
        <label for="cloud_space"><?php echo get_phrase('private'); ?></label></br>
        <input class="" type="radio" checked name="is_private" value="yes" required>yes
        <input class="" type="radio" name="is_private" value="no" required>no
    </div>
    <div class="text-center">
        <button class = "btn btn-success" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
    </div>
</form>
</div>
</div>
</div>
</div>
<script>
    $('.planForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            name: {
                message: 'plan name is not valid',
                validators: {
                    notEmpty: {
                        message: 'Plan name is required and cannot be empty'
                    }
                }
            },
            price: {
                validators: {
                    notEmpty: {
                        message: 'Plan price is required and cannot be empty'
                    }
                }
            },
            courses: {
                validators: {
                    notEmpty: {
                        message: 'Courses value is required and cannot be empty'
                    }
                }
            },
            classes: {
                validators: {
                    notEmpty: {
                        message: 'Classes value is required and cannot be empty'
                    }
                }
            },
            live_minutes: {
                validators: {
                    notEmpty: {
                        message: 'Live minutes value is required and cannot be empty'
                    }
                }
            }
        },
    });
</script>