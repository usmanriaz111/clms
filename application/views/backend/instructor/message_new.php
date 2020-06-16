<?php
    // $student_list = $this->crud_model->all_enrolled_student()->result_array();
	$student_list = $this->crud_model->get_instructor_course_students();
	$instructor_classes = $this->crud_model->curret_user_classes();
?>
<div class="card">
	<h3>
		<span class="p-3"><?php echo get_phrase('write_new_messages');?></span>
	</h3>
	<div class="card-body">
		<form method="post" class="mt-2" action="<?php echo site_url('instructor/message/send_new'); ?>" enctype="multipart/form-data">

		<div class="form-group">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		            	<label><?php echo get_phrase('Recipient Type'); ?></label>
                        <select class="form-control select2" data-toggle="select2" name="message_receiver_type" id="message_receiver_type" required>
							<option value=""><?php echo get_phrase('select_a_class_or_student');?></option>
							<option value="class"><?php echo get_phrase('class');?></option>
							<option value="student"><?php echo get_phrase('student');?></option>
						</select>
		            </div>
		        </div>
		    </div>

		<div class="form-group js-classes">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		            	<label><?php echo get_phrase('Recipient'); ?></label>
                        <select class="form-control select2" data-toggle="select2" name="receiver_class" id="receiver_class">
							<option value=""><?php echo get_phrase('select_a_class');?></option>
                            <?php foreach($instructor_classes as $cls):?>
                                    <option value="<?php echo $cls['id']; ?>">
                                        - <?php echo $cls['name']; ?></option>
                                <?php endforeach; ?>
						</select>

		            </div>
		        </div>
			</div>
			<div class="form-group js-students">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<label><?php echo get_phrase('Recipient'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="receiver_student" id="receiver_student">
							<option value=""><?php echo get_phrase('select_a_user');?></option>
                            <optgroup label="<?php echo get_phrase('students'); ?>">
                                <?php foreach($student_list as $student):?>
                                    <option value="<?php echo $student['id']; ?>">
                                        - <?php echo $student['first_name'].' '.$student['last_name']; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
						</select>
					</div>
				</div>
			</div>

		    <div class="form-group">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		                <textarea class="form-control" rows="5" name="message" id="message" placeholder="<?php echo get_phrase('type_your_message'); ?>" required></textarea>
		            </div>
		        </div>
		    </div>

		    <div class="form-group mt-4">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-13 text-center">
		                <button type="submit" class="btn btn-success float-right"><?php echo get_phrase('sent_message'); ?></button>
		            </div>
		        </div>
		    </div>
		</form>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$('#select2-receiver_class-container').parent().hide();
	$('.js-classes').hide();
	$('#select2-receiver_student-container').parent().hide();
	$('.js-students').hide();
	$('#message_receiver_type').on('change', function(){
		var receiver_type = $(this).val();
		if(receiver_type == 'class'){
		$('#select2-receiver_class-container').parent().show();
		$('.js-classes').show();
		$('#select2-receiver_student-container').parent().hide();
		$('.js-students').hide();
	}
	else if(receiver_type == 'student')
	{
		$('.js-classes').hide();
		$('#select2-receiver_class-container').parent().hide();
		$('.js-students').show();
		$('#select2-receiver_student-container').parent().show();
	}else{
		$('#select2-receiver_class-container').parent().hide();
		$('.js-classes').hide();
		$('#select2-receiver_student-container').parent().hide();
		$('.js-students').hide();
	}
	});
});
	


	function check_receiver() {
		var check_receiver = $('#receiver').val();
		if (check_receiver == '' || check_receiver == 0) {
			toastr.error("Please select a receiver", "Error");
            return false;
		}
	}

</script>
