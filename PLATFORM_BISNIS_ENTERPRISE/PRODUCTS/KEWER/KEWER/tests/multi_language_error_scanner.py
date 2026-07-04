#!/usr/bin/env python3
"""
Multi-Language Error Scanner
Scans files for errors in PHP, JavaScript, CSS, SQL, JSON, and HTML
"""

import os
import re
import subprocess
import json
from pathlib import Path
from typing import List, Dict

class MultiLanguageErrorScanner:
    def __init__(self, base_dir: str):
        self.base_dir = Path(base_dir)
        self.results = {
            'php': {'syntax_errors': [], 'other_issues': []},
            'javascript': {'syntax_errors': [], 'common_issues': []},
            'css': {'syntax_errors': [], 'common_issues': []},
            'sql': {'syntax_errors': [], 'common_issues': []},
            'json': {'parse_errors': [], 'common_issues': []},
            'html': {'validation_errors': [], 'common_issues': []}
        }
        
    def scan_all_files(self):
        """Scan all files for errors"""
        print("Starting comprehensive multi-language error scan...")
        print("=" * 60)
        
        # Scan PHP files
        print("\n1. Scanning PHP files...")
        self.scan_php_files()
        
        # Scan JavaScript files
        print("\n2. Scanning JavaScript files...")
        self.scan_javascript_files()
        
        # Scan CSS files
        print("\n3. Scanning CSS files...")
        self.scan_css_files()
        
        # Scan SQL files
        print("\n4. Scanning SQL files...")
        self.scan_sql_files()
        
        # Scan JSON files
        print("\n5. Scanning JSON files...")
        self.scan_json_files()
        
        # Scan HTML files
        print("\n6. Scanning HTML files...")
        self.scan_html_files()
        
        return self.results
    
    def scan_php_files(self):
        """Scan PHP files for syntax errors"""
        php_files = list(self.base_dir.rglob('*.php'))
        php_files = [f for f in php_files if 'vendor' not in str(f) and 'node_modules' not in str(f)]
        
        print(f"   Found {len(php_files)} PHP files")
        
        for php_file in php_files:
            try:
                result = subprocess.run(
                    ['php', '-l', str(php_file)],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                
                if result.returncode != 0 and 'Parse error' in result.stderr:
                    self.results['php']['syntax_errors'].append({
                        'file': str(php_file),
                        'error': result.stderr.strip()
                    })
                    
            except Exception as e:
                pass  # Skip files that can't be checked
    
    def scan_javascript_files(self):
        """Scan JavaScript files for common issues"""
        js_files = list(self.base_dir.rglob('*.js'))
        js_files = [f for f in js_files if 'node_modules' not in str(f)]
        
        print(f"   Found {len(js_files)} JavaScript files")
        
        for js_file in js_files:
            try:
                with open(js_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                # Check for common JavaScript errors
                self.check_js_issues(js_file, content)
                    
            except Exception as e:
                pass
    
    def check_js_issues(self, js_file: Path, content: str):
        """Check for JavaScript issues"""
        # Check for console.log statements (should be removed in production)
        console_logs = re.finditer(r'console\.log\s*\(', content)
        for match in console_logs:
            line_num = content[:match.start()].count('\n') + 1
            self.results['javascript']['common_issues'].append({
                'file': str(js_file),
                'line': line_num,
                'issue': 'console.log statement found',
                'severity': 'warning'
            })
        
        # Check for var usage (should use let/const)
        var_usage = re.finditer(r'\bvar\s+', content)
        for match in var_usage:
            line_num = content[:match.start()].count('\n') + 1
            self.results['javascript']['common_issues'].append({
                'file': str(js_file),
                'line': line_num,
                'issue': 'var usage - prefer let/const',
                'severity': 'warning'
            })
        
        # Check for == instead of ===
        double_equals = re.finditer(r'[^=]==[^=]', content)
        for match in double_equals:
            line_num = content[:match.start()].count('\n') + 1
            self.results['javascript']['common_issues'].append({
                'file': str(js_file),
                'line': line_num,
                'issue': '== used - prefer ===',
                'severity': 'warning'
            })
    
    def scan_css_files(self):
        """Scan CSS files for common issues"""
        css_files = list(self.base_dir.rglob('*.css'))
        css_files = [f for f in css_files if 'node_modules' not in str(f)]
        
        print(f"   Found {len(css_files)} CSS files")
        
        for css_file in css_files:
            try:
                with open(css_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                # Check for !important usage (should be avoided)
                important_usage = re.finditer(r'!\s*important', content)
                for match in important_usage:
                    line_num = content[:match.start()].count('\n') + 1
                    self.results['css']['common_issues'].append({
                        'file': str(css_file),
                        'line': line_num,
                        'issue': '!important usage - avoid if possible',
                        'severity': 'warning'
                    })
                    
            except Exception as e:
                pass
    
    def scan_sql_files(self):
        """Scan SQL files for common issues"""
        sql_files = list(self.base_dir.rglob('*.sql'))
        
        print(f"   Found {len(sql_files)} SQL files")
        
        for sql_file in sql_files:
            try:
                with open(sql_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                # Check for SELECT * (should specify columns)
                select_star = re.finditer(r'SELECT\s+\*', content, re.IGNORECASE)
                for match in select_star:
                    line_num = content[:match.start()].count('\n') + 1
                    self.results['sql']['common_issues'].append({
                        'file': str(sql_file),
                        'line': line_num,
                        'issue': 'SELECT * used - specify columns',
                        'severity': 'warning'
                    })
                    
            except Exception as e:
                pass
    
    def scan_json_files(self):
        """Scan JSON files for parse errors"""
        json_files = list(self.base_dir.rglob('*.json'))
        json_files = [f for f in json_files if 'node_modules' not in str(f)]
        
        print(f"   Found {len(json_files)} JSON files")
        
        for json_file in json_files:
            try:
                with open(json_file, 'r', encoding='utf-8') as f:
                    json.load(f)
                    
            except json.JSONDecodeError as e:
                self.results['json']['parse_errors'].append({
                    'file': str(json_file),
                    'error': str(e)
                })
            except Exception as e:
                pass
    
    def scan_html_files(self):
        """Scan HTML files for common issues"""
        html_files = list(self.base_dir.rglob('*.html'))
        html_files = [f for f in html_files if 'node_modules' not in str(f)]
        
        print(f"   Found {len(html_files)} HTML files")
        
        for html_file in html_files:
            try:
                with open(html_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                # Check for missing alt attributes on images
                img_tags = re.finditer(r'<img[^>]*>', content, re.IGNORECASE)
                for match in img_tags:
                    img_tag = match.group()
                    if 'alt=' not in img_tag:
                        line_num = content[:match.start()].count('\n') + 1
                        self.results['html']['common_issues'].append({
                            'file': str(html_file),
                            'line': line_num,
                            'issue': 'img tag missing alt attribute',
                            'severity': 'warning'
                        })
                    
            except Exception as e:
                pass
    
    def print_results(self):
        """Print scan results"""
        print("\n" + "=" * 60)
        print("MULTI-LANGUAGE ERROR SCAN RESULTS")
        print("=" * 60)
        
        total_errors = 0
        total_warnings = 0
        
        for lang, results in self.results.items():
            errors = results.get('syntax_errors', []) + results.get('parse_errors', [])
            warnings = results.get('common_issues', [])
            
            if errors or warnings:
                print(f"\n{lang.upper()} ({len(errors)} errors, {len(warnings)} warnings):")
                print("-" * 60)
                
                if errors:
                    print(f"\n  ERRORS ({len(errors)}):")
                    for error in errors[:5]:
                        print(f"    File: {error['file']}")
                        print(f"    Error: {error.get('error', 'Unknown error')}")
                        print()
                    if len(errors) > 5:
                        print(f"    ... and {len(errors) - 5} more errors")
                
                if warnings:
                    print(f"\n  WARNINGS ({len(warnings)}):")
                    for warning in warnings[:5]:
                        print(f"    File: {warning['file']}")
                        print(f"    Line: {warning.get('line', 'N/A')}")
                        print(f"    Issue: {warning['issue']}")
                        print()
                    if len(warnings) > 5:
                        print(f"    ... and {len(warnings) - 5} more warnings")
                
                total_errors += len(errors)
                total_warnings += len(warnings)
        
        print("\n" + "=" * 60)
        print(f"TOTAL: {total_errors} errors, {total_warnings} warnings")
        print("=" * 60)

if __name__ == '__main__':
    scanner = MultiLanguageErrorScanner('/opt/lampp/htdocs/kewer')
    results = scanner.scan_all_files()
    scanner.print_results()
