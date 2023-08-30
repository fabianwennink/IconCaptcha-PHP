# Security Policy

As the maintainer of this open-source self-hosted captcha solution, I take security very seriously. IconCaptcha is designed to provide a secure alternative to third-party captchas, and I encourage responsible disclosure of any vulnerabilities that may be found.

## Reporting a Vulnerability

If you find a security vulnerability in IconCaptcha, please contact me privately by filling out the contact form on my website at https://www.fabianwennink.nl/en/#!contact. Please do not report security vulnerabilities through GitHub issues.

When reporting a vulnerability, please include as much information as possible, including:

- A detailed description of the vulnerability, including the lines of code where the issue is located, steps on how to reproduce it, and any relevant logs, screenshots, or other supporting evidence.
- Information about the potential impact of the vulnerability.
- Your name and contact information, so that I can communicate with you about the issue.

I will acknowledge receipt of your email within 48 hours and work with you to investigate and address the issue.

## Keeping Your Installation Secure

To ensure that your installation of IconCaptcha remains secure, please follow these best practices:

- Keep IconCaptcha up-to-date. Check for new releases regularly and ensure that you are using the latest version. You can stay informed about new releases by subscribing to "Watch" the repository on GitHub.
- Ensure that the server-side validation endpoint used by IconCaptcha is secure.
- Ensure there are no implementation or configuration mistakes in your systems. Follow the documentation closely and test your implementation thoroughly to ensure that everything is working as expected.
- Implement rate limiting, CORS, and other security measures to prevent brute force attacks on the captcha validation endpoint. This will help to prevent malicious actors from making repeated attempts to bypass the captcha.
- Regularly audit your website/server logs for suspicious activity. This will allow you to identify potential security issues early on and take action to address them.

## Disclosure

Please note that support is limited to the default installation of IconCaptcha. Custom implementations or modifications made to the source code of IconCaptcha, including those related to the custom drivers and hooks, are not covered by this security policy.

Any such customizations or modifications are the sole responsibility of the user and may impact the security of IconCaptcha. By using IconCaptcha, you acknowledge and agree that the maintainer is not responsible for resolving any security vulnerabilities or issues resulting from customizations or modifications to the source code.

Thank you for your understanding and helping to keep IconCaptcha secure.
