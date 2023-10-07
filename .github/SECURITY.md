# Security Policy

As the maintainer of this open-source self-hosted captcha solution, I take security very seriously. IconCaptcha is designed to provide a secure alternative to third-party captchas, and I encourage responsible disclosure of any vulnerabilities that may be found.

## Supported Versions

To ensure the  level of security and reliability for IconCaptcha, only the latest major version of IconCaptcha will receive official support. This means that I will provide updates, bug fixes, and security patches exclusively for the most recent major release. 
When implementing or updating IconCaptcha, I highly encourage using the most recent minor and patch release available. These releases often include critical bug fixes and additional security measures. Failing to update to the latest minor or patch release may expose your implementation to potential security vulnerabilities.

## Reporting a Vulnerability

If you find a security vulnerability in IconCaptcha, please contact me privately by filling out the contact form on my website at https://www.fabianwennink.nl/en/#!contact. Please do not report security vulnerabilities through GitHub issues.

When reporting a vulnerability, please include as much information as possible, including:

- A detailed description of the vulnerability, including the lines of code where the issue is located, steps on how to reproduce it, and any relevant logs, screenshots, or other supporting evidence.
- Information about the potential impact of the vulnerability.
- Your name and contact information, so that I can communicate with you about the issue.

I will acknowledge receipt of your email within 48 hours and work with you to investigate and address the issue.

## Keeping Your Installation Secure

To ensure that your installation of IconCaptcha remains secure, please follow these best practices:

- Keep your IconCaptcha implementation up-to-date. Check for new releases regularly and ensure that you are using the latest version available. You can stay informed about new releases by subscribing to "Watch" the repository on GitHub.
- Ensure that your implementation of the validation endpoint used by IconCaptcha is secure.
- Ensure there are no implementation or configuration mistakes in your systems. Follow the documentation closely and test your implementation thoroughly to ensure that everything is working as expected.
- Implement rate limiting, CORS and other security measures to prevent brute force attacks on the captcha validation endpoint. This will help to prevent malicious actors from making repeated attempts to bypass the captcha.
- Regularly audit your application and server logs for suspicious activity. This will allow you to identify potential security issues early on and take action to address them.

## Disclosure

Please be advised that the scope of support is strictly limited to the default installation of IconCaptcha. Any modifications made to the source code and installations that deviate from using the classes and options available in the default source code are explicitly excluded from the coverage provided by this security policy.

Any such customizations or modifications made to the source code of IconCaptcha are the sole responsibility of the user and may impact the security of IconCaptcha. By using IconCaptcha, you acknowledge and agree that the maintainer is not responsible for addressing and resolving any security vulnerabilities or issues arising from such customizations or modifications to the source code.

Thank you for your understanding and helping to keep IconCaptcha secure.
