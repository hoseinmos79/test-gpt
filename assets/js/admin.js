jQuery(document).ready(function($) {
    // Handle form field selection
    $('input[name="form_fields[]"]').on('change', function() {
        var fieldKey = $(this).val();
        var requiredCheckbox = $('input[name="required_fields[]"][value="' + fieldKey + '"]');
        
        if ($(this).is(':checked')) {
            requiredCheckbox.prop('disabled', false);
        } else {
            requiredCheckbox.prop('checked', false).prop('disabled', true);
        }
    });
    
    // Initialize form field states
    $('input[name="form_fields[]"]').each(function() {
        var fieldKey = $(this).val();
        var requiredCheckbox = $('input[name="required_fields[]"][value="' + fieldKey + '"]');
        
        if (!$(this).is(':checked')) {
            requiredCheckbox.prop('disabled', true);
        }
    });
    
    // Handle form name validation
    $('input[name="form_name"]').on('input', function() {
        var value = $(this).val();
        var cleanValue = value.replace(/[^a-zA-Z0-9_]/g, '');
        
        if (value !== cleanValue) {
            $(this).val(cleanValue);
        }
    });
    
    // Add confirmation for delete actions
    $('.smsfb-delete-form').on('click', function(e) {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این فرم را حذف کنید؟')) {
            e.preventDefault();
        }
    });
    
    // Handle copy shortcode functionality
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        var button = $(this);
        
        // Create temporary textarea
        var textarea = $('<textarea>').val(shortcode).appendTo('body').select();
        document.execCommand('copy');
        textarea.remove();
        
        // Show feedback
        var originalText = button.text();
        button.text('کپی شد!').prop('disabled', true);
        
        setTimeout(function() {
            button.text(originalText).prop('disabled', false);
        }, 2000);
    });
    
    // Handle test SMS functionality
    $('#test-sms').on('click', function() {
        var button = $(this);
        var result = $('#test-result');
        
        button.prop('disabled', true).text('در حال ارسال...');
        result.html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'smsfb_test_sms',
                nonce: smsfb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    result.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                result.html('<div class="notice notice-error"><p>خطا در اتصال</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('ارسال پیامک تست');
            }
        });
    });
    
    // Add form validation
    $('.smsfb-add-form form').on('submit', function(e) {
        var formName = $('input[name="form_name"]').val();
        var formFields = $('input[name="form_fields[]"]:checked');
        
        if (!formName) {
            alert('لطفاً نام فرم را وارد کنید.');
            e.preventDefault();
            return;
        }
        
        if (formFields.length === 0) {
            alert('لطفاً حداقل یک فیلد را انتخاب کنید.');
            e.preventDefault();
            return;
        }
        
        // Check if form name already exists
        var existingForms = [];
        $('.smsfb-forms-list table tbody tr').each(function() {
            existingForms.push($(this).find('td:first').text().trim());
        });
        
        if (existingForms.includes(formName)) {
            alert('فرمی با این نام قبلاً وجود دارد. لطفاً نام دیگری انتخاب کنید.');
            e.preventDefault();
            return;
        }
    });
});