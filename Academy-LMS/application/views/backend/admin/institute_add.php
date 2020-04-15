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

                <h4 class="header-title mb-3"><?php echo get_phrase('institute_add_form'); ?></h4>

<form action="<?php echo site_url('admin/institutes/'.$param1.'add'); ?>" method="post">
    <div class="form-group">
        <label for="title"><?php echo get_phrase('institute_name'); ?></label>
        <input class="form-control" type="text" name="name" id="name" required>
    </div>
    <div class="form-group">
        <label for="title"><?php echo get_phrase('institute_phone_number'); ?></label>
        <input class="form-control" type="text" name="phone_number" id="phone_number" required>
    </div>
    <div class="form-group">
        <label for="title"><?php echo get_phrase('institute_address'); ?></label>
        <input class="form-control" type="text" name="address" id="address" required>
    </div>
    <div class="form-group">
        <label><?php echo get_phrase('description'); ?></label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="text-center">
        <button class = "btn btn-success" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
    </div>
</form>
</div>
</div>
</div>
</div>

