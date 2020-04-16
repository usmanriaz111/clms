<?php
    $payment_histories = $this->crud_model->get_instructor_wise_payment_history($this->session->userdata('user_id'));
?>

<!-- start page title -->
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('instructor_revenue'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3 header-title"><?php echo get_phrase('instructor_revenue'); ?></h4>
                <div class="table-responsive-sm mt-4">
                    <table id="basic-datatable" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th><?php echo get_phrase('enrolled_course'); ?></th>
                                <th><?php echo get_phrase('instructor_revenue'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('option'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_history as $payment):
                                $course_data = $this->db->get_where('course', array('id' => $payment['course_id']))->row_array();
                                $user_data = $this->db->get_where('users', array('id' => $course_data['user_id']))->row_array();?>
                                <?php
                                $paypal_keys          = json_decode($user_data['paypal_keys'], true);
                                $stripe_keys          = json_decode($user_data['stripe_keys'], true);
                                ?>
                                <tr class="gradeU">
                                    <td>
                                        <strong><a href="<?php echo site_url('home/course/'.slugify($course_data['title']).'/'.$course_data['id']); ?>" target="_blank"><?php echo ellipsis($course_data['title']); ?></a></strong><br>
                                        <small class="text-muted"><?php echo get_phrase('enrolment_date').': '.date('D, d-M-Y', $payment['date_added']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo currency($payment['instructor_revenue']); ?><br>
                                        <small class="text-muted"><?php echo get_phrase('total_amount').': '.currency($payment['amount']); ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($payment['instructor_payment_status'] == 0): ?>
                                            <div class="badge badge-danger"><?php echo get_phrase('pending'); ?></div>
                                        <?php elseif($payment['instructor_payment_status'] == 1): ?>
                                            <div class="badge badge-success"><?php echo get_phrase('paid'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo site_url('user/invoice/'.$payment['id']); ?>" class="btn btn-outline-primary btn-rounded btn-sm"><i class="mdi mdi-printer-settings"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function update_date_range()
{
    var x = $("#selectedValue").html();
    $("#date_range").val(x);
}
</script>
