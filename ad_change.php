<?php

/*
 * Author: Archie Gunasekara
 * Date: 01.05.2015
 * Make sure your PHP install has both the ldap and openssl extensions enabled.
 * Windows/Linux Procedure
 * Verify the ldap.conf file settings.
 * For Windows, verify that the C:\openldap\sysconf\ldap.conf file exists.
 * For Linux, verify that the /etc/openldap/ldap.conf file exists. If it does not, create it.
 * For both Linux and Windows, the ldap.conf file should contain this line:
 * TLS_REQCERT     never
 * If you want php to verify the ldap server's ssl certificate with the Certificate Authority that issued the certificate, you need to put the root certificate here:
 * Export the trusted root Certificate. (For details, see Step 1 in How to test LDAP over SSL).
 * Use this command to convert the DER to PEM:
 * openssl x509 -in RootCert.der -inform DER -out RootCert.pem -outform PEM
 * On Windows you can download openssl binaries from these two sites:
 * http://gnuwin32.sourceforge.net/packages.html
 * http://www.ShininglightPro.com/
 * Now copy the rootcert.pem to the certs folder:
 * For Linux, /etc/openldap/cert/rootcert.pem
 * For Windows, C:\openldap\sysconf\certs\rootcert.pem
 * For both Linux and Windows, the ldap.conf file should contain this line:
 * (Linux)  TLS_CACERT /etc/openldap/cert/rootcert.pem
 * (Windows) TLS_CACERT c:\OpenLDAP\sysconf\certs\rootcert.pem
 * If there are errors, add print_r for the output and examine the error
 */

//Configuration items
$user_name = $argv[1];
$password = $argv[2];

//go change the password
print change_ad_password($user_name, $password);

function create_ldap_connection($ldaps_url, $port, $admin_user, $admin_pass, $bind_dn) {

        $ldap_conn = ldap_connect($ldaps_url, $port) or die("Fatal Error: " . ldap_error($ldap_conn));

        if(!ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3)){

                echo ldap_error($ldap_conn);
                die("Could not set LDAPv3\r\n");
        }
        else if (!ldap_start_tls($ldap_conn)) {

                echo ldap_error($ldap_conn);
                die("Could not start secure TLS connection");
        }

        $binddn = "CN=$admin_user," . $bind_dn;
        $result = ldap_bind($ldap_conn, $binddn, $admin_pass);

        if($result) {

                return $ldap_conn;
        }
        else {

                print ldap_error($ldap_conn) . "\n";
                print ldap_errno($ldap_conn) . "\n";
                die("Error: Couldn't bind to server with supplied credentials!");
        }
}

function get_user_dn($ldap_conn, $user_name, $base_dn, $ou_list) {

                $info = array();
                $ou_arr = explode(",", $ou_list);

        for($i = 0; $i < count($ou_arr); $i++) {

                /*Write the below details as per your AD setting*/
                $basedn = "OU=" . $ou_arr[$i]  . ",$base_dn";

                /*Search the user details in AD server*/
                $searchResults = ldap_search($ldap_conn, $basedn, $user_name);

                if(!is_resource($searchResults)) die('Error in search results.');

                /*Get the first entry from the searched result*/
                $entry = ldap_first_entry($ldap_conn, $searchResults);

                $info = ldap_get_entries($ldap_conn, $searchResults);
                echo $info["count"]." entries returned for OU " . $ou_arr[$i]  . " \n";
                print "\n\n";

                if($info["count"] > 0) {

                        break;
                }
        }

        return ldap_get_dn($ldap_conn, $entry);
}

function pwd_encryption($newPassword) {

        $newPassword = "\"" . $newPassword . "\"";
        $newPassw = mb_convert_encoding($newPassword, "UTF-16LE");
        $userdata["unicodePwd"] = $newPassw;
        return $userdata;
}

function change_ad_password($user_name, $password) {


        //Application Configuration
        $ldaps_url = "ad_dns_name";
        $ldap_port = "389";
        $admin_user = "admin_user";
        $admin_pass = "admin_password";
        $bind_dn = "CN=Users,DC=ad,DC=domain,DC=com";
        $base_dn = "OU=OU_NAME,DC=ad,DC=domain,DC=com";
        //comma seperated list of OUs
        $ou_list = "Sub_OU_1,Sub_OU_2,Sub_OU_3,Sub_OU_4";

        $filter = "(|(SamAccountName=$user_name*))";
        $ldap_conn = create_ldap_connection($ldaps_url, $ldap_port, $admin_user, $admin_pass, $bind_dn);
        $userDn = get_user_dn($ldap_conn, $filter, $base_dn, $ou_list);
        $userdata = pwd_encryption($password);

        //output information
        print_r($userDn);
        print "\n\n";
        print_r($userdata);
        print "\n\n";

        $result = ldap_modify($ldap_conn, $userDn , $userdata);

        if($result) {

                echo "Success attempting to modify password in AD\n";
                return "Active Directory password was Changed Successfully!";
        }
        else {

                echo "Error: Please try again later!\n";
                $e = ldap_error($ldap_conn);
                $e_no = ldap_errno($ldap_conn);
                echo $e . "\n";
                echo $e_no . "\n";
                return "AD password failed to change!";
        }
}

?>
