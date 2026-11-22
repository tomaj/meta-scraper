# Security Policy

## Supported Versions

Security fixes are provided for the lines below.

| Version | Supported |
| ------- | --------- |
| 5.x     | Yes, once 5.0.0 is released: bug fixes and security fixes |
| 4.x     | Bug fixes and security fixes until 5.0.0 is released, then security fixes only for six months |
| < 4.0   | No |

4.x is the current stable line. When 5.0.0 is released, 4.x gets fixes for security vulnerabilities only, for six months after the 5.0.0 release; there is no 4.x maintenance branch, so a fix is released from one created from the `4.3.0` tag. Versions older than 4.0 do not receive security updates.

## Reporting a Vulnerability

Do not open a public GitHub issue for a security vulnerability.

The preferred way to report a vulnerability is [GitHub private vulnerability reporting](https://github.com/tomaj/meta-scraper/security/advisories/new).

If you cannot use private vulnerability reporting, email the details to **tomasmajer@gmail.com**.

Please include:

- A description of the vulnerability
- Steps to reproduce, or a proof of concept
- The meta-scraper version and the parser involved
- The potential impact
- A suggested fix, if you have one

### Response

You will receive an acknowledgement within 14 days.

### Coordinated disclosure

We follow coordinated disclosure. Please give us time to prepare a fix before discussing the issue in public.

When a fix is ready, we publish a [GitHub Security Advisory](https://github.com/tomaj/meta-scraper/security/advisories) and record the security fix in the [CHANGELOG](CHANGELOG.md).

## Security considerations

meta-scraper parses HTML that usually comes from third-party websites, and `Scraper::parseUrl()` downloads whatever URL it is given:

- **Server-side request forgery (SSRF).** Do not pass user-supplied URLs to `parseUrl()` unchecked. An attacker could make your server request internal addresses (`localhost`, `169.254.169.254` cloud metadata, private networks) or follow a redirect there. Validate the scheme and the resolved IP address, disable or limit redirects (`allow_redirects` Guzzle option) and use a proxy that blocks internal networks when the URL comes from users.
- **Resource limits.** Set Guzzle `timeout` and `connect_timeout`, and limit the response size if you fetch untrusted hosts; the default only sets a 5 second connect timeout.
- **Untrusted output.** Parsed titles, descriptions, URLs and images come from the page author. Escape them before you render them in HTML and validate URLs (for example the `og:image` scheme) before you follow or embed them.
