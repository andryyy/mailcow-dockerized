---
name: Bug report
about: Report a bug for this project. NOT to be used for support questions.

---

<!--
  Please DO NOT delete this template or use it for support questions.
  You are welcome to visit us on our community channels listed at https://mailcow.github.io/mailcow-dockerized-docs/#community-support
  For official support, please check https://mailcow.github.io/mailcow-dockerized-docs/#commercial-support
-->

**Prior to placing the issue, please check following:** *(fill out each checkbox with an `X` once done)*
- [ ] I understand, that not following or deleting the below instructions, will result in immediate closing and deletion of my issue.
- [ ] I have understood that answers are voluntary and community-driven, and not commercial support.
- [ ] I have verified that my issue has not been already answered in the past. I also checked previous [issues](https://github.com/mailcow/mailcow-dockerized/issues).

**Description of the bug**:
<!--
  This should be a clear and concise description of what the bug is. What EXACTLY does happen?
  If applicable, add screenshots to help explain your problem. Very useful for bugs in mailcow UI.
  Write your detailed description below.
-->

**Docker container logs of affected containers**:
<!--
  Please take a look at the [official documentation](https://mailcow.github.io/mailcow-dockerized-docs/debug-logs/) and post the last     few lines of logs, when the error occurs.
-->

**Reproduction of said bug**:
<!--
  It is really helpful to know how exactly you are able to reproduce the reported issue.
  Have you tried to fix the issue? What did you try?
  What are the exact steps to get the above described behavior?
  Screenshots can be added, if helpful. Add the text below.
-->

**System information**:
<!--
  In this stage we would kindly ask you to attach general system information about your setup.
  Please carefully read the questions and instructions below.
-->

| Question | Answer |
| --- | --- |
| My operating system | I_DO_REPLY_HERE |
| Is Apparmor, SELinux or similar active? | I_DO_REPLY_HERE |
| Virtualization technlogy (KVM, VMware, Xen, etc - **LXC and OpenVZ are not supported** | I_DO_REPLY_HERE |
| Server/VM specifications (Memory, CPU Cores) | I_DO_REPLY_HERE |
| Docker Version (`docker version`) | I_DO_REPLY_HERE |
| Docker-Compose Version (`docker-compose version`) | I_DO_REPLY_HERE |
| Reverse proxy (custom solution) | I_DO_REPLY_HERE |

- Output of `git diff origin/master`, any other changes to the code? If so, **please post them**.
- All third-party firewalls and custom iptables rules are unsupported. *Please check the Docker docs about how to use Docker with your own ruleset*. Nevertheless, iptabels output can help us to help you: `iptables -L -vn`, `ip6tables -L -vn`, `iptables -L -vn -t nat` and `ip6tables -L -vn -t nat`.
- DNS problems? Please run `docker exec -it $(docker ps -qf name=acme-mailcow) dig +short stackoverflow.com @172.22.1.254` (set the IP accordingly, if you changed the internal mailcow network) and post the output.

