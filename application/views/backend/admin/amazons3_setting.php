<!-- start page title -->
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('setup_S3_informations'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-md-7" style="padding: 0;">
        <!-- System Currency Settings -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><p><?php echo 'AWS S3 Storage'; ?></p></h4>
                    <?php
                        $settings = $this->db->get_where('s3_settings', array('user_id' => $this->session->userdata('user_id')))->row_array();
                    ?>
                    <?php if ( in_array( $this->session->userdata('user_id') ,$settings )){?>
                                        
                    <form class="" action="<?php echo site_url('admin/amazons3_setting/edit/'.$settings['id']); ?>" method="post" enctype="multipart/form-data">
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
                        <form class="" action="<?php echo site_url('admin/amazons3_setting/add'); ?>" method="post" enctype="multipart/form-data">
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
</div>


