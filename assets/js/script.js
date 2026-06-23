document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('custom-comments-system-container');
    if (!container) return;

    const form = container.querySelector('.custom-form');
    if (!form) return;

    const submitBtn = form.querySelector('.submit-btn');
    
    // Create notice element and append it below the form
    const noticeElement = document.createElement('div');
    noticeElement.className = 'comment-submit-notice';
    form.appendChild(noticeElement);

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic client-side validation
        const fullNameInput = form.querySelector('#fullName');
        const emailInput = form.querySelector('#email');
        const messageInput = form.querySelector('#message');

        const fullName = fullNameInput ? fullNameInput.value.trim() : '';
        const email = emailInput ? emailInput.value.trim() : '';
        const message = messageInput ? messageInput.value.trim() : '';

        if (!fullName || !email || !message) {
            showNotice('لطفاً تمام فیلدها را پر کنید.', 'error');
            return;
        }

        // Prepare form data
        const formData = new FormData(form);
        formData.append('action', 'custom_submit_comment');
        
        // Disable button and update text
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'در حال ارسال...';
        }
        
        hideNotice();

        // Get admin AJAX URL from localized object
        if (typeof custom_comment_ajax_obj === 'undefined' || !custom_comment_ajax_obj.ajax_url) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'ثبت';
            }
            showNotice('خطا در بارگذاری پیکربندی سیستم نظرات.', 'error');
            return;
        }

        const ajaxUrl = custom_comment_ajax_obj.ajax_url;

        // Perform AJAX request
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'ثبت';
            }

            if (data.success) {
                showNotice(data.data.message || 'نظر شما با موفقیت ثبت شد.', 'success');
                
                // Clear inputs
                if (fullNameInput) fullNameInput.value = '';
                if (emailInput) emailInput.value = '';
                if (messageInput) messageInput.value = '';

                // Dynamically prepend new comment if approved and HTML returned
                if (data.data.comment_html) {
                    let commentsSection = container.querySelector('.comments-section');
                    if (!commentsSection) {
                        // Create comments section dynamically if it didn't exist
                        commentsSection = document.createElement('section');
                        commentsSection.className = 'comments-section';
                        container.appendChild(commentsSection);
                    }

                    let commentsList = commentsSection.querySelector('.comments-list');
                    if (!commentsList) {
                        commentsList = document.createElement('ul');
                        commentsList.className = 'comments-list';
                        commentsSection.appendChild(commentsList);
                    }

                    // Create li item for new comment
                    const newCommentLi = document.createElement('li');
                    newCommentLi.className = 'comment-item';
                    newCommentLi.innerHTML = data.data.comment_html;
                    
                    // Prepend new comment
                    commentsList.insertBefore(newCommentLi, commentsList.firstChild);
                }
            } else {
                showNotice(data.data.message || 'خطایی در ثبت نظر رخ داد.', 'error');
            }
        })
        .catch(error => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'ثبت';
            }
            showNotice('ارتباط با سرور برقرار نشد. لطفاً دوباره تلاش کنید.', 'error');
            console.error('Error submitting comment:', error);
        });
    });

    function showNotice(text, type) {
        noticeElement.innerText = text;
        noticeElement.className = 'comment-submit-notice comment-submit-notice--' + type;
        noticeElement.style.display = 'block';
        
        // Scroll slightly if needed to keep notice in view
        noticeElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideNotice() {
        noticeElement.style.display = 'none';
        noticeElement.innerText = '';
    }
});
