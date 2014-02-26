title: linux sudo ����������ļ�/etc/sudoers����
date: 2014-02-26 17:40:34
id: 1402261740
tags:
- linux
- sudo
categories: 
- ϵͳ���/linux

---

`sudo` ��**linux**�³��õ�������ͨ�û�ʹ�ó����û�Ȩ�޵Ĺ��ߡ� ���������ļ� **sudoers** һ���� **/etc** Ŀ¼�¡�

�������� **sudoers** �ļ����Ķ���`sudo` ���ṩ��һ���༭���ļ������**visudo** ���Ը��ļ������޸ġ�ǿ���Ƽ�ʹ�ø������޸� **sudoers**����Ϊ�������У���ļ������Ƿ���ȷ���������ȷ���ڱ����˳�ʱ�ͻ���ʾ���Ķ����ó���ġ� 

������ **sudoers** ��Ĭ�������ļ����ݡ�

<!--more-->

```
#
# This file MUST be edited with the 'visudo' command as root.
#
# Please consider adding local content in /etc/sudoers.d/ instead of
# directly modifying this file.
#
# See the man page for details on how to write a sudoers file.
#
Defaults        env_reset
Defaults        secure_path="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# Host alias specification

# User alias specification

# Cmnd alias specification

# User privilege specification
root    ALL=(ALL:ALL) ALL

# Members of the admin group may gain root privileges
%admin ALL=(ALL) ALL

# Allow members of group sudo to execute any command
%sudo   ALL=(ALL:ALL) ALL

```

### �������ø�ʽ

```
<user list> <host list> = <operator list> <tag list> <command list>
```

* *user list*       �û�/�飬�����Ѿ����õ��û��ı����б�, �û���ֱ�� `username`���û������`%`������`%admin`,
* *host list*       ������������б�
* *operator list*   runas�û������������ĸ��û������Ȩ����ִ��
* *command list*    ����ִ�е�������б�
* *tag list*        ��������õ����� **NOPASSWD:** ������������֮����Բ����������롣

��������Ĭ����������Ķ�Ӧ���õ�˵��

```
## root �û������������������������û������Ȩ��ִ���������˵��root�û�������߼���Ȩ�ޡ�
root    ALL=(ALL:ALL) ALL

## admin����û���ӵ����߼�Ȩ�ޡ�
%admin ALL=(ALL) ALL

## sudo ���û���root�û�Ȩ��һ��
%sudo   ALL=(ALL:ALL) ALL
```

**tag list** ����� **NOPASSWD:** ������ʱ����Ҫ������������:

```
## test �û����Բ��������������� **/sbin/reboot** ����
test ALL=(ALL) NOPASSWD: /sbin/reboot
```

 **operator list** �� **tag list** ���ǿ�ѡ�ģ���������ģ�

```
test ALL=/sbin/reboot
```

ʵ�����ӣ� ��`PHP`��ʹ��`system` ����ϵͳ����ʱ��������`php`һ�㶼��ʹ��`www`�û����еģ����Ҫ�ر����Ҫ����Ҫ����ĳ���û����ļ�ʱ�Ϳ���ָ��ĳ��Ŀ¼�����ʹ��ĳ���û�Ȩ�����������С�

���ӣ� Ŀ¼ **/mnt/sudodir** ����ĳ������ʹ��rootȨ����ִ�С�

```
www ALL=(root) NOPASSWD: /mnt/sudodir
```

ʹ��ʱ��ֱ��`sudo -u root /mnt/sudodir/cmd`������Ҫ�������롣

Ϊ�˰�ȫ�������� **/mnt/sudodir** ����`root`�û�֮��������Ӧ��ֻ�����е�Ȩ�ޡ�

�鿴��ǰ�û���`sudo`Ȩ�޿���ʹ������ `sudo -l`

PS�� ��������Щ���ϣ���Ҫ��Ϊ��ͨ��[github](https://github.com/)��webhook����, ����ҵ�VPS�ϵ�PHP�ű���ʵ�� `hexo` վ����Զ����£�������ֻ��Ҫ����Դ�ļ���github�ϣ������Զ�����վ�����ݣ������Զ�ͬ���������ͬ�ĵط����п��һ�����һƪ���������������

�ο����ϣ� <https://help.ubuntu.com/community/Sudoers>

