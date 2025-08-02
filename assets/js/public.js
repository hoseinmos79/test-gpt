jQuery(document).ready(function($) {
    // Handle form submission
    $('.smsfb-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formContainer = form.closest('.smsfb-form-container');
        var formName = formContainer.data('form-name');
        var submitBtn = form.find('.smsfb-submit-btn');
        var messageDiv = form.find('.smsfb-form-message');
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('در حال ارسال...');
        messageDiv.html('');
        
        // Collect form data
        var formData = {};
        form.find('input, textarea, select').each(function() {
            var field = $(this);
            var name = field.attr('name');
            var value = field.val();
            
            if (name && value) {
                formData[name] = value;
            }
        });
        
        // Send AJAX request
        $.ajax({
            url: smsfb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smsfb_submit_form',
                nonce: smsfb_ajax.nonce,
                form_name: formName,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<div class="smsfb-success">' + response.data.message + '</div>');
                    form[0].reset();
                    
                    // Reset city select
                    form.find('.city-select').html('<option value="">ابتدا استان را انتخاب کنید</option>').prop('disabled', true);
                } else {
                    messageDiv.html('<div class="smsfb-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                messageDiv.html('<div class="smsfb-error">خطا در اتصال به سرور</div>');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('ارسال فرم');
            }
        });
    });
    
    // Handle province change for city dropdown
    $('.province-select').on('change', function() {
        var province = $(this).val();
        var citySelect = $(this).closest('.smsfb-field-group').next().find('.city-select');
        
        if (province) {
            // Enable city select
            citySelect.prop('disabled', false);
            
            // Load cities
            $.ajax({
                url: smsfb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'smsfb_get_cities',
                    nonce: smsfb_ajax.nonce,
                    province: province
                },
                success: function(response) {
                    if (response.success) {
                        var cities = response.data;
                        var options = '<option value="">انتخاب شهر</option>';
                        
                        cities.forEach(function(city) {
                            options += '<option value="' + city + '">' + city + '</option>';
                        });
                        
                        citySelect.html(options);
                    }
                },
                error: function() {
                    citySelect.html('<option value="">خطا در بارگذاری شهرها</option>');
                }
            });
        } else {
            // Disable city select
            citySelect.html('<option value="">ابتدا استان را انتخاب کنید</option>').prop('disabled', true);
        }
    });
    
    // Add loading state to form fields
    $('.smsfb-form input, .smsfb-form textarea, .smsfb-form select').on('focus', function() {
        $(this).addClass('smsfb-field-focus');
    }).on('blur', function() {
        $(this).removeClass('smsfb-field-focus');
    });
});