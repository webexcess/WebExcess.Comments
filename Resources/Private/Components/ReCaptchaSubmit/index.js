const ReCaptchaSubmit = (token) => {
    const formId = 'comment-form';
    const formFieldId = 'reCaptchaToken';

    document.getElementById(formFieldId).value = token;
    document.getElementById(formId).submit();
};

export default ReCaptchaSubmit;
