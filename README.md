##ReadMe

Archie Gunasekara - 01.05.2015

1. Make sure your PHP install has both the ldap and openssl extensions enabled.
2. Windows/Linux Procedure
3. Verify the ldap.conf file settings.
4. For Windows, verify that the C:\openldap\sysconf\ldap.conf file exists.
5. For Linux, verify that the /etc/openldap/ldap.conf file exists. If it does not, create it.
6. For both Linux and Windows, the ldap.conf file should contain this line:
** -TLS_REQCERT     never
8. If you want php to verify the ldap server's ssl certificate with the Certificate Authority that issued the certificate, you need to put the root certificate here:
9. Export the trusted root Certificate. (For details, see Step 1 in How to test LDAP over SSL).
10. Use this command to convert the DER to PEM:
11. openssl x509 -in RootCert.der -inform DER -out RootCert.pem -outform PEM
12. On Windows you can download openssl binaries from these two sites:
13. http://gnuwin32.sourceforge.net/packages.html
14. http://www.ShininglightPro.com/
15. Now copy the rootcert.pem to the certs folder:
16. For Linux, /etc/openldap/cert/rootcert.pem
17. For Windows, C:\openldap\sysconf\certs\rootcert.pem
18. For both Linux and Windows, the ldap.conf file should contain this line:
19. (Linux)  TLS_CACERT /etc/openldap/cert/rootcert.pem
20. (Windows) TLS_CACERT c:\OpenLDAP\sysconf\certs\rootcert.pem