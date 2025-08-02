<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$form_name = $atts['name'];
$form_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}smsfb_forms WHERE form_name = %s",
    $form_name
));

if (!$form_data) {
    echo '<p>فرم مورد نظر یافت نشد.</p>';
    return;
}

$form_fields = json_decode($form_data->form_fields, true);
$required_fields = json_decode($form_data->required_fields, true);

$field_labels = array(
    'first_name' => 'نام',
    'last_name' => 'نام خانوادگی',
    'phone' => 'شماره تماس',
    'email' => 'ایمیل',
    'description' => 'توضیح کوتاه',
    'province' => 'استان',
    'city' => 'شهر'
);

$provinces = array(
    'آذربایجان شرقی' => 'آذربایجان شرقی',
    'آذربایجان غربی' => 'آذربایجان غربی',
    'اردبیل' => 'اردبیل',
    'اصفهان' => 'اصفهان',
    'البرز' => 'البرز',
    'ایلام' => 'ایلام',
    'بوشهر' => 'بوشهر',
    'تهران' => 'تهران',
    'چهارمحال و بختیاری' => 'چهارمحال و بختیاری',
    'خراسان جنوبی' => 'خراسان جنوبی',
    'خراسان رضوی' => 'خراسان رضوی',
    'خراسان شمالی' => 'خراسان شمالی',
    'خوزستان' => 'خوزستان',
    'زنجان' => 'زنجان',
    'سمنان' => 'سمنان',
    'سیستان و بلوچستان' => 'سیستان و بلوچستان',
    'فارس' => 'فارس',
    'قزوین' => 'قزوین',
    'قم' => 'قم',
    'کردستان' => 'کردستان',
    'کرمان' => 'کرمان',
    'کرمانشاه' => 'کرمانشاه',
    'کهگیلویه و بویراحمد' => 'کهگیلویه و بویراحمد',
    'گلستان' => 'گلستان',
    'گیلان' => 'گیلان',
    'لرستان' => 'لرستان',
    'مازندران' => 'مازندران',
    'مرکزی' => 'مرکزی',
    'هرمزگان' => 'هرمزگان',
    'همدان' => 'همدان',
    'یزد' => 'یزد'
);
?>

<div class="smsfb-form-container" data-form-name="<?php echo esc_attr($form_name); ?>">
    <form class="smsfb-form" id="smsfb-form-<?php echo esc_attr($form_name); ?>">
        <div class="smsfb-form-fields">
            <?php foreach ($form_fields as $field): ?>
                <div class="smsfb-field-group">
                    <label for="<?php echo esc_attr($field); ?>">
                        <?php echo esc_html($field_labels[$field]); ?>
                        <?php if (in_array($field, $required_fields)): ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    
                    <?php if ($field === 'description'): ?>
                        <textarea 
                            name="<?php echo esc_attr($field); ?>" 
                            id="<?php echo esc_attr($field); ?>"
                            <?php echo in_array($field, $required_fields) ? 'required' : ''; ?>
                            placeholder="<?php echo esc_attr($field_labels[$field]); ?>"
                        ></textarea>
                    <?php elseif ($field === 'province'): ?>
                        <select 
                            name="<?php echo esc_attr($field); ?>" 
                            id="<?php echo esc_attr($field); ?>"
                            <?php echo in_array($field, $required_fields) ? 'required' : ''; ?>
                            class="province-select"
                        >
                            <option value="" disabled selected>انتخاب استان</option>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo esc_attr($province); ?>"><?php echo esc_html($province); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($field === 'city'): ?>
                        <select 
                            name="<?php echo esc_attr($field); ?>" 
                            id="<?php echo esc_attr($field); ?>"
                            <?php echo in_array($field, $required_fields) ? 'required' : ''; ?>
                            class="city-select"
                            disabled
                        >
                            <option value="">ابتدا استان را انتخاب کنید</option>
                        </select>
                    <?php else: ?>
                        <input 
                            type="<?php echo $field === 'email' ? 'email' : 'text'; ?>" 
                            name="<?php echo esc_attr($field); ?>" 
                            id="<?php echo esc_attr($field); ?>"
                            <?php echo in_array($field, $required_fields) ? 'required' : ''; ?>
                            placeholder="<?php echo esc_attr($field_labels[$field]); ?>"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="smsfb-form-submit">
            <button type="submit" class="smsfb-submit-btn">ارسال فرم</button>
        </div>
        
        <div class="smsfb-form-message"></div>
    </form>
</div>