<?php
    // $student_list = $this->crud_model->all_enrolled_student()->result_array();
	$student_list = $this->user_model->get_user()->result_array();
	$instructor_classes = $this->crud_model->curret_user_classes();
?>
<div class="card">
	<h3>
		<span class="p-3"><?php echo get_phrase('write_new_messages');?></span>
	</h3>
	<div class="card-body">
		<form method="post" class="mt-2" action="<?php echo site_url('institute/message/send_new'); ?>" enctype="multipart/form-data">

		<div class="form-group">
          <label><?php echo get_phrase('instructor'); ?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="instructors" id="instructors" required>
            <?php foreach ($instructors as $instructor): ?>
            <option value="<?php echo $instructor['id']; ?>"><?php echo $instructor['first_name'].' '.$instructor['last_name'] ; ?></option>
            <?php endforeach; ?>
            </select>
    </div>
    <div class="form-group">
          <label><?php echo get_phrase('select_course'); ?><span class="required">*</span></label>
         <select class="form-control select2" data-toggle="select2" name="courses" id="courses" required>
		 <?php foreach ($courses as $course): ?>
            <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
            <?php endforeach; ?>
            </select>
	</div>

	<div class="form-group">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		            	<label><?php echo get_phrase('Recipient Type'); ?><span class="required">*</span></label>
                        <select class="form-control select2" data-toggle="select2" name="message_receiver_type" id="message_receiver_type" required>
							<option value=""><?php echo get_phrase('select_a_class_or_student');?></option>
							<option value="class"><?php echo get_phrase('class');?></option>
							<option value="student"><?php echo get_phrase('student');?></option>
						</select>
		            </div>
		        </div>
		    </div>
	
	<div class="form-group js-classes">
          <label><?php echo get_phrase('select_class'); ?><span class="required">*</span></label>
         <select class="form-control" data-toggle="select2" name="receiver_class" id="classes">
            
        </select>
    </div>

			<div class="form-group js-students">
		        <div class="row">
		            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<label><?php echo get_phrase('Recipient'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="receiver_student" id="receiver_student">
							<option value=""><?php echo get_phrase('select_a_user');?></option>
                            
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

<script type="text/javascript">
  $(document).ready(function () {
    sync_courses();
    $('#instructors').on('change', function(){
        sync_courses();
});
sync_classes();
sync_students();
$('#courses').on('change', function(){
        sync_classes();
		sync_students();
});

function sync_courses(){
    let id = $("#instructors option:selected").val();

        $.ajax({
        url : "<?php echo base_url();?>institute/ajax_sync_course",
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
            sync_classes();
        },
        error : function(response) {
            console.log(response);
        }
    });
}


function sync_classes(){
    let id = $("#courses option:selected").val();
	console.log(id);
        $.ajax({
        url : "<?php echo base_url();?>institute/ajax_sync_classes",
        type : "post",
        dataType : "json",
        data : {"course_id" : id},
        success : function(response) {
            var select = document.getElementById("classes");
            var length = select.options.length;
            for (i = length-1; i >= 0; i--) {
            select.options[i] = null;
            }
            $.each( response, function( i, val ) {
                var newState = new Option(val.name, val.id);
                $("#classes").append(newState);
            });
            sync_students();
        },
        error : function(response) {
            console.log(response);
        }
    });
}

function sync_students(){
    let id = $("#courses option:selected").val();
	console.log(id);
        $.ajax({
        url : "<?php echo base_url();?>institute/ajax_sync_students",
        type : "post",
        dataType : "json",
        data : {"course_id" : id},
        success : function(response) {
            var select = document.getElementById("receiver_student");
            var length = select.options.length;
            for (i = length-1; i >= 0; i--) {
            select.options[i] = null;
            }
            $.each( response, function( i, val ) {
                var newState = new Option(val.first_name+' '+val.last_name, val.id);
                $("#receiver_student").append(newState);
            });
        },
        error : function(response) {
            console.log(response);
        }
    });
}

  });
  </script>