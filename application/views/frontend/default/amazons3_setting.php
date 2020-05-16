<?php
$this->db->where('user_id', $this->session->userdata('user_id'));
$purchase_history = $this->db->get('payment',$per_page, $this->uri->segment(3));
?>
<section class="page-header-area my-course-area">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title"><?php echo get_phrase('purchase_history'); ?></h1>
                <ul>
                    <li><a href="<?php echo site_url('home/my_courses'); ?>"><?php echo get_phrase('all_courses'); ?></a></li>
                    <li><a href="<?php echo site_url('home/my_wishlist'); ?>"><?php echo get_phrase('wishlists'); ?></a></li>
                    <li><a href="<?php echo site_url('home/my_messages'); ?>"><?php echo get_phrase('my_messages'); ?></a></li>
                    <li><a href="<?php echo site_url('home/purchase_history'); ?>"><?php echo get_phrase('purchase_history'); ?></a></li>
                    <li class="active"  ><a href="<?php echo site_url('home/amazons3_setting_form/add_form'); ?>"><?php echo get_phrase('s3_settngs'); ?></a></li>
                    <li><a href="<?php echo site_url('home/profile/user_profile'); ?>"><?php echo get_phrase('user_profile'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</section>


<section class="purchase-history-list-area">
    <div class="container">
        <div class="row">
        <div class="col-md-3"></div>
            <div class="col-md-6">
            <?php
                        $settings = $this->db->get_where('s3_settings', array('user_id' => $this->session->userdata('user_id')))->row_array();
                    ?>
                    <?php if ( in_array( $this->session->userdata('user_id') ,$settings )){?>
                                        
                    <form class="" action="<?php echo site_url('home/amazons3_setting/edit/'.$settings['id']); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>AWS Access Key</label>
                            <input type="text" class="form-control" id="aws_access_key" value="<?php echo $settings['access_key']?>" name="aws_access_key"  required>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>AWS Secret Key</label>
                        <input type="text" class="form-control" id="aws_secret_key" value="<?php echo $settings['secret_key']?>" name="aws_secret_key"  required>
                    </div>

                    <div class="form-group">
                        <label>Region</label>
                        <input type="text" class="form-control" id="region" value="<?php echo $settings['region']?>" name="region"  required>
                    </div>

                    <div class="form-group">
                        <label>AWS Base URL</label>
                        <input type="url" class="form-control" id="aws_url" value="<?php echo $settings['url']?>" name="aws_url"  required>
                    </div>

                    <div class="form-group">
                        <label>Bucket Name</label>
                        <input type="text" class="form-control" id="bucket_name" value="<?php echo $settings['bucket_name']?>" name="bucket_name"  required>
                    </div>

                    <div class="row justify-content-md-center">
                        <div class="form-group col-md-6">
                            <button class="btn btn-block btn-primary" type="submit"><?php echo get_phrase('save_changes'); ?></button>
                        </div>
                    </div>
                </form>
                    <?php } else{ ?>
                        <form class="" action="<?php echo site_url('home/amazons3_setting/add'); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>AWS Access Key</label>
                            <input type="text" class="form-control" id="aws_access_key" name="aws_access_key"  required>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>AWS Secret Key</label>
                        <input type="text" class="form-control" id="aws_secret_key" name="aws_secret_key"  required>
                    </div>

                    <div class="form-group">
                        <label>Region</label>
                        <input type="text" class="form-control" id="region" name="region"  required>
                    </div>

                    <div class="form-group">
                        <label>AWS Base URL</label>
                        <input type="url" class="form-control" id="aws_url" name="aws_url"  required>
                    </div>

                    <div class="form-group">
                        <label>Bucket Name</label>
                        <input type="text" class="form-control" id="bucket_name" name="bucket_name"  required>
                    </div>

                    <div class="row justify-content-md-center">
                        <div class="form-group col-md-6">
                            <button class="btn btn-block btn-primary" type="submit"><?php echo get_phrase('save_changes'); ?></button>
                        </div>
                    </div>
                </form>
                    <?php }?>

            </div>
        </div>
    </div>
</section>
<?php
  if(addon_status('offline_payment') == 1):
    include "pending_purchase_course_history.php";
  endif;
?>
<nav>
    <?php echo $this->pagination->create_links(); ?>
</nav>


