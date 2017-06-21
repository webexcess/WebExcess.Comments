import smoothScroll from 'smoothscroll';

const ReplyButton = () => {
    const formFieldId = 'reference';
    const buttonClass = '.comment-reply-button';

    return (
        document.querySelectorAll(buttonClass).forEach(buttonNode => {
            buttonNode.addEventListener('click', (event) => {
                let button = event.target;
                document.getElementById(formFieldId).value = button.getAttribute('data-identifier');
                smoothScroll(document.getElementById(formFieldId));
            })
        })
    )
};

export default ReplyButton;