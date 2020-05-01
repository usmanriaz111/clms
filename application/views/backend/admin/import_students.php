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
                <h4 class="header-title mb-3"><?php echo get_phrase('import_students'); ?></h4>
              <form class="form-inline" action="<?php echo base_url(); ?>import/importFile" method="post" enctype="multipart/form-data">
                    Upload Students Excel file :
                    <input type="hidden" name='class_id' value="<?php echo $class_id?>"/>
                    <input type="file" name="uploadFile" value="" class="pull-left" /><br><br>
                    <input type="submit" name="submit" class="btn btn-primary" value="Upload" />
                </form>
</div>
</div>
</div>
</div>