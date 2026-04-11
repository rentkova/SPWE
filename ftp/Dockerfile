FROM fauria/vsftpd

ENV TZ=Europe/Prague

RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*

RUN yum -y update && \
    curl -L -o pam_mysql.rpm https://raw.githubusercontent.com/anthcourtney/pam-mysql/master/pam_mysql-0.7-0.16.rc1.el7.centos.x86_64.rpm && \
    yum -y localinstall pam_mysql.rpm && \
    rm -f pam_mysql.rpm && \
    yum clean all

RUN mkdir -p /var/log/vsftpd

RUN echo "chroot_local_user=YES" >> /etc/vsftpd/vsftpd.conf && \
    echo "allow_writeable_chroot=YES" >> /etc/vsftpd/vsftpd.conf && \
    echo "xferlog_enable=YES" >> /etc/vsftpd/vsftpd.conf && \
    echo "log_ftp_protocol=YES" >> /etc/vsftpd/vsftpd.conf

RUN echo "guest_enable=YES" >> /etc/vsftpd/vsftpd.conf && \
    echo "guest_username=ftp" >> /etc/vsftpd/vsftpd.conf && \
    echo "virtual_use_local_privs=YES" >> /etc/vsftpd/vsftpd.conf && \
    echo "user_sub_token=\$USER" >> /etc/vsftpd/vsftpd.conf && \
    echo "user_config_dir=/etc/vsftpd/user_conf" >> /etc/vsftpd/vsftpd.conf && \
    echo "local_root=/www/\$USER" >> /etc/vsftpd/vsftpd.conf && \
    echo "pam_service_name=vsftpd.mysql" >> /etc/vsftpd/vsftpd.conf

RUN mkdir -p /etc/vsftpd/user_conf && \
    echo "local_root=/www" > /etc/vsftpd/user_conf/ftpuser

#pokud chceme mít zahashovné heslo musíme přepsat crypt=0 na crypt=1 a v databázi mít hesla zahashovaná pomocí MD5, SHA1 nebo SHA256
RUN echo "auth required pam_mysql.so user=root passwd=Heslo host=hosting_db db=hosting_centrum table=domeny usercolumn=ftp_uzivatel passwdcolumn=ftp_heslo_hash crypt=0" > /etc/pam.d/vsftpd.mysql && \
    echo "account required pam_mysql.so user=root passwd=Heslo host=hosting_db db=hosting_centrum table=domeny usercolumn=ftp_uzivatel passwdcolumn=ftp_heslo_hash crypt=0" >> /etc/pam.d/vsftpd.mysql

EXPOSE 21 21100-21110