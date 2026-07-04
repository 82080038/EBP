#!/usr/bin/env python3
"""
Comprehensive PHP Analysis for Kewer Application
Scans for common issues based on analysis.md patterns
"""

import os
import re
from pathlib import Path
from collections import defaultdict

class PHPAnalyzer:
    def __init__(self, base_path):
        self.base_path = Path(base_path)
        self.issues = defaultdict(list)
        self.stats = {
            'files_scanned': 0,
            'api_files': 0,
            'page_files': 0,
            'include_files': 0,
            'total_issues': 0
        }
        
        # Common SQL column mismatches from analysis.md
        self.column_mismatches = {
            r'cabang\.nama\b': 'cabang.nama_cabang',
            r'pembayaran\.metode\b': 'pembayaran.cara_bayar',
            r'pembayaran\.dibayar_oleh\b': 'pembayaran.petugas_id',
            r'angsuran\.ke\b': 'angsuran.no_angsuran',
            r'c\.nama\b': 'c.nama_cabang',
        }
        
        # Hardcoded paths to avoid
        self.hardcoded_paths = [
            r'/opt/lampp/htdocs/kewer',
            r'c:\\xampp\\htdocs\\kewer',
        ]
        
    def scan_directory(self, directory):
        """Scan all PHP files in directory"""
        php_files = list(self.base_path.glob(f"{directory}/**/*.php"))
        for php_file in php_files:
            self.scan_file(php_file)
        return php_files
    
    def scan_file(self, file_path):
        """Analyze a single PHP file"""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
                lines = content.split('\n')
        except Exception as e:
            print(f"Error reading {file_path}: {e}")
            return
        
        self.stats['files_scanned'] += 1
        
        # Categorize file
        if 'api/' in str(file_path):
            self.stats['api_files'] += 1
        elif 'pages/' in str(file_path):
            self.stats['page_files'] += 1
        elif 'includes/' in str(file_path):
            self.stats['include_files'] += 1
        
        # Run all checks
        self.check_sql_column_mismatches(file_path, lines)
        self.check_undefined_variables(file_path, lines, content)
        self.check_double_navbar(file_path, content)
        self.check_hardcoded_paths(file_path, lines)
        self.check_array_access(file_path, lines)
        self.check_sql_injection(file_path, lines)
        self.check_missing_includes(file_path, content)
        self.check_frekuensi_enum(file_path, content)
        self.check_feature_flags(file_path, content)
    
    def check_sql_column_mismatches(self, file_path, lines):
        """Check for SQL column name mismatches"""
        for i, line in enumerate(lines, 1):
            for pattern, correct in self.column_mismatches.items():
                if re.search(pattern, line):
                    self.issues['column_mismatch'].append({
                        'file': str(file_path),
                        'line': i,
                        'issue': f"SQL column mismatch: found {pattern}, should be {correct}",
                        'code': line.strip()
                    })
    
    def check_undefined_variables(self, file_path, lines, content):
        """Check for undefined variables in templates"""
        # Check for echo $error/$success without initialization
        has_post_block = re.search(r'if\s*\(\$_POST\)', content)
        has_echo_error = re.search(r'echo\s+\$error', content)
        has_echo_success = re.search(r'echo\s+\$success', content)
        
        if (has_echo_error or has_echo_success) and has_post_block:
            # Check if variables are initialized before POST block
            lines_before_post = []
            in_post_block = False
            for line in lines:
                if 'if ($_POST' in line:
                    in_post_block = True
                    break
                lines_before_post.append(line)
            
            initialized = any('$error = ' in line or '$success = ' in line for line in lines_before_post)
            
            if not initialized and (has_echo_error or has_echo_success):
                self.issues['undefined_variable'].append({
                    'file': str(file_path),
                    'line': 0,
                    'issue': "Variables $error/$success used without initialization before POST block",
                    'code': "echo $error/$success without initialization"
                })
    
    def check_double_navbar(self, file_path, content):
        """Check for double navbar (sidebar.php + inline nav)"""
        if 'sidebar.php' in content:
            nav_count = len(re.findall(r'<nav[^>]*>', content, re.IGNORECASE))
            if nav_count > 1:
                self.issues['double_navbar'].append({
                    'file': str(file_path),
                    'line': 0,
                    'issue': f"Double navbar detected ({nav_count} <nav> tags with sidebar.php)",
                    'code': "Multiple <nav> tags"
                })
    
    def check_hardcoded_paths(self, file_path, lines):
        """Check for hardcoded paths"""
        for i, line in enumerate(lines, 1):
            for pattern in self.hardcoded_paths:
                if re.search(pattern, line):
                    self.issues['hardcoded_path'].append({
                        'file': str(file_path),
                        'line': i,
                        'issue': f"Hardcoded path found: {pattern}",
                        'code': line.strip()
                    })
    
    def check_array_access(self, file_path, lines):
        """Check for array access without checks"""
        for i, line in enumerate(lines, 1):
            # Pattern: query(...)[0]['field'] without is_array check
            if re.search(r'query\s*\([^)]+\)\[0\]', line):
                if 'is_array' not in line and 'isset' not in line:
                    self.issues['unsafe_array_access'].append({
                        'file': str(file_path),
                        'line': i,
                        'issue': "Array access without is_array/isset check",
                        'code': line.strip()
                    })
    
    def check_sql_injection(self, file_path, lines):
        """Check for potential SQL injection"""
        for i, line in enumerate(lines, 1):
            # Pattern: query("... $var ...") without prepared statement
            if re.search(r'query\s*\(\s*"[^"]*\$[^"]+"\s*\)', line):
                if '?' not in line or '[' not in line:  # Not using prepared statement
                    self.issues['sql_injection_risk'].append({
                        'file': str(file_path),
                        'line': i,
                        'issue': "Potential SQL injection - variable directly in query string",
                        'code': line.strip()
                    })
    
    def check_missing_includes(self, file_path, content):
        """Check for missing critical includes"""
        # API files should include functions.php
        if 'api/' in str(file_path):
            if 'functions.php' not in content and 'hasPermission' in content:
                self.issues['missing_include'].append({
                    'file': str(file_path),
                    'line': 0,
                    'issue': "Using hasPermission() without including functions.php",
                    'code': "hasPermission() without functions.php"
                })
    
    def check_frekuensi_enum(self, file_path, content):
        """Check for deprecated frekuensi enum usage (v2.4.0)"""
        if "frekuensi enum" in content or "ENUM('harian'" in content.lower():
            self.issues['deprecated_enum'].append({
                'file': str(file_path),
                'line': 0,
                'issue': "Deprecated frekuensi enum - should use frekuensi_id (INT)",
                'code': "ENUM('harian','mingguan','bulanan')"
            })
        
        # Check for column 'frekuensi' usage
        if re.search(r'frekuensi\s*,', content) or re.search(r'frekuensi\s+FROM', content):
            self.issues['deprecated_enum'].append({
                'file': str(file_path),
                'line': 0,
                'issue': "Column 'frekuensi' deprecated - should use 'frekuensi_id'",
                'code': "SELECT frekuensi FROM ..."
            })
    
    def check_feature_flags(self, file_path, content):
        """Check for new features without feature flags"""
        # WA notification usage
        if 'wa_notifikasi' in content or 'sendWhatsApp' in content:
            if 'isFeatureEnabled' not in content:
                self.issues['missing_feature_flag'].append({
                    'file': str(file_path),
                    'line': 0,
                    'issue': "WA notification used without feature flag check",
                    'code': "sendWhatsApp() without isFeatureEnabled('wa_notifikasi')"
                })
        
        # GPS usage
        if 'geolocation' in content or 'navigator.geolocation' in content:
            if 'isFeatureEnabled' not in content:
                self.issues['missing_feature_flag'].append({
                    'file': str(file_path),
                    'line': 0,
                    'issue': "GPS tracking used without feature flag check",
                    'code': "geolocation without isFeatureEnabled('gps_pembayaran')"
                })
    
    def print_report(self):
        """Print comprehensive report"""
        print("\n" + "="*80)
        print("COMPREHENSIVE PHP ANALYSIS REPORT")
        print("="*80)
        
        print(f"\n📊 STATISTICS:")
        print(f"   Files scanned: {self.stats['files_scanned']}")
        print(f"   API files: {self.stats['api_files']}")
        print(f"   Page files: {self.stats['page_files']}")
        print(f"   Include files: {self.stats['include_files']}")
        
        total_issues = sum(len(issues) for issues in self.issues.values())
        self.stats['total_issues'] = total_issues
        
        print(f"\n   Total issues found: {total_issues}")
        
        if total_issues == 0:
            print("\n✅ No issues found!")
            return
        
        print(f"\n📋 ISSUES BY CATEGORY:")
        for category, issues in sorted(self.issues.items()):
            if issues:
                print(f"\n   {category.upper().replace('_', ' ')}: {len(issues)} issues")
                
                # Show first 5 issues per category
                for i, issue in enumerate(issues[:5], 1):
                    rel_path = issue['file'].replace(str(self.base_path) + '/', '')
                    print(f"\n   {i}. {rel_path}:{issue['line']}")
                    print(f"      Issue: {issue['issue']}")
                    if issue['code']:
                        print(f"      Code: {issue['code'][:100]}")
                
                if len(issues) > 5:
                    print(f"   ... and {len(issues) - 5} more")
        
        print("\n" + "="*80)

if __name__ == '__main__':
    import sys
    base_path = sys.argv[1] if len(sys.argv) > 1 else '/opt/lampp/htdocs/kewer'
    
    analyzer = PHPAnalyzer(base_path)
    
    print("🔍 Scanning PHP files...")
    analyzer.scan_directory('api')
    analyzer.scan_directory('pages')
    analyzer.scan_directory('includes')
    analyzer.scan_directory('models')
    
    analyzer.print_report()
