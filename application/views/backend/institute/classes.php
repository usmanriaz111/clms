
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                <a href = "<?php echo site_url('institute/class_form/add_class_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_class'); ?></a>
            </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
              <h4 class="mb-3 header-title"><?php echo get_phrase('classes'); ?></h4>
              <!-- filter form here-->
              <div class="table-responsive-sm mt-4">
                <table id="basic-datatable" class="table table-striped table-centered mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th><?php echo get_phrase('name'); ?></th>
                      <th><?php echo get_phrase('course_name'); ?></th>
                      <th><?php echo get_phrase('no_of_student'); ?></th>
                      <th><?php echo get_phrase('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                       foreach ($classes as $key => $cls): ?>
                        <tr>
                        <?php
                        $course = $this->crud_model->get_course_by_id($cls['course_id'])->row_array();
                        $enrolled_students = $this->user_model->get_class_enrolled_students($cls['id'])->num_rows();

                        ?>
                            <td><?php echo $key+1; ?></td>
                            <td><strong><a href="<?php echo site_url('institute/class_form/edit_class_form/'.$cls['id']) ?>"><?php echo get_phrase($cls['name']); ?></a></strong></td>
                            <td><?php echo $course['title']; ?></td>
                            <td><?php echo $enrolled_students?>
                            <!-- <a href = "<?php echo site_url('institute/class_id/'.$cls['id'].'/add_student'); ?>" class=""><span class="mdi mdi-24px mdi-account-plus"></span></a> -->
                            <!-- <a href="<?php echo site_url('Institute/import_students/class_id/'.$cls['id']); ?>"><span class="mdi mdi-24px mdi-file-import"></span></a> -->
                        </td>
                        <!-- <td><a href = "<?php echo site_url('institute/class_id/'.$cls['id'].'/users'); ?>" class="">View Students</a></td> -->
                            <td>
                                  <div class="dropright dropright">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item"     href = "<?php echo site_url('institute/class_id/'.$cls['id'].'/users'); ?>" class="">View Students</a></li>
                                        <li><a class="dropdown-item" href="<?php echo site_url('Institute/import_students/class_id/'.$cls['id']); ?>"><?php echo get_phrase('import_students'); ?></a></li>
                                        <li><a class="dropdown-item" href="<?php echo site_url('institute/class_id/'.$cls['id'].'/add_student'); ?>"><?php echo get_phrase('add_student'); ?></a></li>
                                        <li><a class="dropdown-item" href="<?php echo site_url('institute/class_form/edit_class_form/'.$cls['id']) ?>"><?php echo get_phrase('edit'); ?></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('institute/classes/delete/'.$cls['id']); ?>');"><?php echo get_phrase('delete'); ?></a></li>
                                    </ul>
                                </div>
                              </td>
                        </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
              </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
