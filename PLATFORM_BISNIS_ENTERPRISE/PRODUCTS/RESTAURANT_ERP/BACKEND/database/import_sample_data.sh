#!/bin/bash
mysql -u ebp_app -pebp_secure_password_2026 -S /opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/sample_accounting_data.sql
