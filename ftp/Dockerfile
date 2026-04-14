FROM fauria/vsftpd

ENV TZ=Europe/Prague

# Oprava zastaralých CentOS repozitářů
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*

# Instalace pam_mysql
RUN yum -y update && \
    curl -L -o /tmp/pam_mysql.rpm https://raw.githubusercontent.com/anthcourtney/pam-mysql/master/pam_mysql-0.7-0.16.rc1.el7.centos.x86_64.rpm && \
    yum -y localinstall /tmp/pam_mysql.rpm && \
    rm -f /tmp/pam_mysql.rpm && \
    yum clean all && \
    mkdir -p /var/log/vsftpd /www

# Přidat pouze chybějící vsftpd direktivy pro virtuální uživatele
# pasv_*, pam_service_name a local_root přepíše start-vsftpd.sh přes sed
RUN printf '%s\n' \
    'chroot_local_user=YES' \
    'allow_writeable_chroot=YES' \
    'guest_enable=YES' \
    'guest_username=ftp' \
    'virtual_use_local_privs=YES' \
    'user_sub_token=$USER' \
    'local_root=/www/$USER' \
    'accept_timeout=60' \
    'connect_timeout=60' \
    >> /etc/vsftpd/vsftpd.conf

# Startovací skript – přepíše duplicitní hodnoty přes sed a sestaví PAM konfiguraci
COPY start-vsftpd.sh /usr/local/bin/start-vsftpd.sh
RUN chmod +x /usr/local/bin/start-vsftpd.sh

CMD ["/usr/local/bin/start-vsftpd.sh"]
