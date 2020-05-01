    <?php
    $classes = $this->db->get('classes')->result_array();
    ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
    <h4 class="header-title mb-3"><?php echo get_phrase('create_live_session'); ?></h4>
        <div class="form-group">
            <label for="name"><?php echo get_phrase('session_name'); ?></label>
            <input class="form-control" type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="classes"><?php echo get_phrase('select a class'); ?></label>
            <select class="form-control select2" data-toggle="select2" name="live_class_session" id="section_id" required>
                <?php foreach ( $classes as $class): ?>
                    <option value="<?php echo $class['id']; ?>"><?php echo $class['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="cloud_space"><?php echo get_phrase('live_session_time_in_mins'); ?></label>
            <input class="form-control" type="number" name="time" id="time" required>
        </div>

        <div class="text-center">
            <button class = "btn btn-success" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
        </div>

    </div>
    </div>
    </div>
    </div>
