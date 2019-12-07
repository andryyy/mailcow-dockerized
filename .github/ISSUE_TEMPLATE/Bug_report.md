---
name: Bug report
about: Report a bug for this project

---
<!--
  For community support and other discussions, you are welcome to visit us on our community channels listed at https://mailcow.github.io/mailcow-dockerized-docs/#community-support. For professional commercial support, please check out https://mailcow.github.io/mailcow-dockerized-docs/#commercial-support instead
-->

Please **try** to answer all questions. If you cannot, don't just delete it. If you completely ignore or remove the whole template, your issue is either closed or ignored, too. :( This is (not only) because we are evil. Many awesome mailcow supporters don't bother to reply to an issue, when crucial information is missing. Especially when it could have been answered by using this issue template. We need as much information as possible. Please post all logs, do not copy that one line you think may be interesting. Copy the whole bunch and remove sensible information. :) Thank you!

**Prior to placing the issue, please check following:** *(fill out each checkbox with a `X` once done)*
- [ ] I understand that not following below instructions might result in immediate closing and deletion of my issue.
- [ ] I have understood that answers are voluntary and community-driven, and not commercial support.
- [ ] I have verified that my issue has not been already answered in the past. I also checked previous [issues](https://github.com/mailcow/mailcow-dockerized/issues).

---

**Description of the bug**: What kind of issue have you *exactly* come across?
<!--
  This should be a clear and concise description of what the bug is. What EXACTLY does happen?
  If applicable, add screenshots to help explain your problem. Very useful for bugs in mailcow UI.
  Write your detailed description below.
-->

My issue is...

**Reproduction of said bug**: How *exactly* do you reproduce the bug?
<!--
  Here it is really helpful to know how exactly you are able to reproduce the reported issue.
  Meaning: What are the exact steps - one by one - to get the above described behavior.
  Screenshots can be added, if helpful. Add the text below.
-->

1. I go to...
2. And then to...
3. But once I do...

__I have tried or I do...__ *(fill out each checkbox with a `X` if applicable)*
- [ ] In case of WebUI issue, I have tried clearing the browser cache and the issue persists.
- [ ] I do run mailcow on a Synology, QNAP or any other sort of NAS.

**System information**
<!--
  In this stage we would kindly ask you to attach logs or general system information about your setup.
  Please carefully read the questions and instructions below.
-->

Further information (where applicable):

| Question | Answer |
| --- | --- |
| My operating system | I_DO_REPLY_HERE |
| Is Apparmor, SELinux or similar active? | I_DO_REPLY_HERE |
| Virtualization technlogy (KVM, VMware, Xen, etc) | I_DO_REPLY_HERE |
| Server/VM specifications (Memory, CPU Cores) | I_DO_REPLY_HERE |
| Docker Version (`docker version`) | I_DO_REPLY_HERE |
| Docker-Compose Version (`docker-compose version`) | I_DO_REPLY_HERE |
| Reverse proxy (custom solution) | I_DO_REPLY_HERE |

Further notes:
 - Output of `git diff origin/master`, any other changes to the code? If so, please post them.
 - All third-party firewalls and custom iptables rules are unsupported. Please check the Docker docs about how to use Docker with your own ruleset. Nevertheless, iptabels output can help _us_ to help _you_: `iptables -L -vn`, `ip6tables -L -vn`, `iptables -L -vn -t nat` and `ip6tables -L -vn -t nat `
 - Check `docker exec -it $(docker ps -qf name=acme-mailcow) dig +short stackoverflow.com @172.22.1.254` (set the IP accordingly, if you changed the internal mailcow network) and `docker exec -it $(docker ps -qf name=acme-mailcow) dig +short stackoverflow.com @1.1.1.1` - output? Timeout?
 
 General logs:
- Please take a look at the [official documentation](https://mailcow.github.io/mailcow-dockerized-docs/debug-logs/).
