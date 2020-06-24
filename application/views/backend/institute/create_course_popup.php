<form class="required-form" action="<?php echo site_url('institute/course_actions/add'); ?>" method="post" enctype="multipart/form-data">
  <div class="form-group">
  <label class="col-form-label" for="course_title"><?php echo get_phrase('course_title'); ?> <span class="required">*</span> </label>
      <input type="text" class="form-control" id="course_title" name = "title" placeholder="<?php echo get_phrase('enter_course_title'); ?>" required>
  </div>
  <div class="form-group">
  <label class="col-form-label" for="sub_category_id"><?php echo get_phrase('category'); ?><span class="required">*</span></label>
      <select class="form-control select2" data-toggle="select2" name="sub_category_id" id="sub_category_id" required>
          <option value=""><?php echo get_phrase('select_a_category'); ?></option>
          <?php foreach ($categories->result_array() as $category): ?>
              <optgroup label="<?php echo $category['name']; ?>">
                  <?php $sub_categories = $this->crud_model->get_sub_categories($category['id']);
                  foreach ($sub_categories as $sub_category): ?>
                  <option value="<?php echo $sub_category['id']; ?>"><?php echo $sub_category['name']; ?></option>
              <?php endforeach; ?>
          </optgroup>
      <?php endforeach; ?>
  </select>
</div>
<div class="form-group">
<label class="col-md-2 col-form-label" for="type"><?php echo get_phrase('type'); ?> <span class="required">*</span> </label>
<input type="radio" id="public" name="type" value="public" checked >
<label for="public">Public</label>
<input type="radio" id="private" name="type" value="private">
<label for="private">Private</label>
</div>
<div class="form-group">
    <label class="col-form-label" for="instructor"><?php echo get_phrase('instructor'); ?><span class="required">*</span></label>
    <select class="form-control select2" data-toggle="select2" name="instructors" id="instructors">
    <?php foreach ($instructors as $instructor): ?>
    <option value="<?php echo $instructor['id']; ?>"><?php echo $instructor['first_name'].' '.$instructor['last_name'];?></option>
    <?php endforeach; ?>
    </select>
</div>
<div class="text-left">
<button class="btn btn-secondary" data-dismiss="modal"><?php echo get_phrase("close"); ?></button>
</div>
<div class="text-right">
<button type="button" class="btn btn-primary text-right" onclick="checkRequiredFields()"><?php echo get_phrase('submit'); ?></button>
    </div>
</form>

<script type="text/javascript">
  $(document).ready(function () {
    sync_instructors();
    $('#institutes').on('change', function(){
        sync_instructors();
});

function sync_instructors(){
    let id = $("#institutes option:selected").val();
        $.ajax({
        url : "<?php echo base_url();?>admin/ajax_get_instructor",
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
        },
        error : function(response) {
            console.log(response);
        }
    });
}

  });
</script>