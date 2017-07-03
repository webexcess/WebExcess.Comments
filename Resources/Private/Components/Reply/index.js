const ReplyButton = () => {
    const formId = 'comment-form';
    const formFieldId = 'reference';
    const buttonClass = '.comment-reply-button';

    return (
        document.querySelectorAll(buttonClass).forEach(buttonNode => {
            buttonNode.addEventListener('click', (event) => {
                let button = event.target;
                document.getElementById(formFieldId).value = button.getAttribute('data-identifier');

                let form = document.getElementById(formId);
                let clonedForm = form.cloneNode(true);
                form.parentNode.removeChild(form);
                button.parentNode.appendChild(clonedForm);
                // button.parentNode.removeChild(button);

                document.querySelectorAll('.comment-replies-action').forEach(element => {
                    element.classList.remove('has-form');
                });

                button.parentNode.classList.add('has-form');
            })
        })
    )
};

export default ReplyButton;