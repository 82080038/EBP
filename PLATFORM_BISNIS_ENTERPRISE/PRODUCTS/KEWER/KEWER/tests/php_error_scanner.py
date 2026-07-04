#!/usr/bin/env python3
"""
PHP Error Scanner
Scans PHP files for common errors and issues
"""

import os
import re
import subprocess
from pathlib import Path
from typing import List, Dict, Tuple

class PHPErrorScanner:
    def __init__(self, base_dir: str):
        self.base_dir = Path(base_dir)
        self.errors = []
        self.warnings = []
        
    def scan_all_php_files(self) -> Dict[str, List]:
        """Scan all PHP files for errors"""
        results = {
            'syntax_errors': [],
            'undefined_functions': [],
            'missing_includes': [],
            'deprecated_features': [],
            'security_issues': [],
            'best_practices': []
        }
        
        php_files = list(self.base_dir.rglob('*.php'))
        print(f"Scanning {len(php_files)} PHP files...")
        
        for php_file in php_files:
            if 'vendor' in str(php_file) or 'node_modules' in str(php_file):
                continue
                
            try:
                with open(php_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                # Check syntax errors using PHP lint
                syntax_error = self.check_syntax(php_file)
                if syntax_error:
                    results['syntax_errors'].append({
                        'file': str(php_file),
                        'error': syntax_error
                    })
                
                # Check for common issues
                self.check_undefined_functions(php_file, content, results)
                self.check_missing_includes(php_file, content, results)
                self.check_deprecated_features(php_file, content, results)
                self.check_security_issues(php_file, content, results)
                self.check_best_practices(php_file, content, results)
                
            except Exception as e:
                print(f"Error scanning {php_file}: {e}")
                
        return results
    
    def check_syntax(self, php_file: Path) -> str:
        """Check PHP syntax using php -l"""
        try:
            result = subprocess.run(
                ['php', '-l', str(php_file)],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            if result.returncode != 0:
                return result.stderr.strip()
                
        except Exception as e:
            return f"Syntax check failed: {str(e)}"
            
        return None
    
    def check_undefined_functions(self, php_file: Path, content: str, results: Dict):
        """Check for potentially undefined function calls"""
        # Common undefined function patterns
        undefined_patterns = [
            r'\bcatatJurnalKas\s*\(',
            r'\bpostJurnalPinjaman\s*\(',
            r'\bpostJurnalPembayaran\s*\(',
            r'\bpostJurnalPengeluaran\s*\(',
        ]
        
        for pattern in undefined_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                results['undefined_functions'].append({
                    'file': str(php_file),
                    'line': line_num,
                    'function': match.group().replace('(', ''),
                    'pattern': pattern
                })
    
    def check_missing_includes(self, php_file: Path, content: str, results: Dict):
        """Check for files that use functions without proper includes"""
        # Files that use business_logic functions
        if 'catatJurnalKas(' in content or 'postJurnalPinjaman(' in content:
            if 'business_logic.php' not in content:
                line_num = 1
                for match in re.finditer(r'catatJurnalKas\s*\(', content):
                    line_num = content[:match.start()].count('\n') + 1
                    results['missing_includes'].append({
                        'file': str(php_file),
                        'line': line_num,
                        'missing': 'business_logic.php',
                        'function': 'catatJurnalKas'
                    })
                    break
    
    def check_deprecated_features(self, php_file: Path, content: str, results: Dict):
        """Check for deprecated PHP features"""
        deprecated_patterns = [
            (r'\bmysql_', 'mysql_* functions are deprecated, use mysqli or PDO'),
            (r'\berg\b\(', 'ereg() is deprecated, use preg_match()'),
            (r'\bsplit\b\(', 'split() is deprecated, use explode() or preg_split()'),
        ]
        
        for pattern, message in deprecated_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                results['deprecated_features'].append({
                    'file': str(php_file),
                    'line': line_num,
                    'pattern': match.group(),
                    'message': message
                })
    
    def check_security_issues(self, php_file: Path, content: str, results: Dict):
        """Check for security issues"""
        security_patterns = [
            (r'\$_GET\[[^\]]+\]', 'Direct use of $_GET without sanitization'),
            (r'\$_POST\[[^\]]+\]', 'Direct use of $_POST without sanitization'),
            (r'\$_REQUEST\[[^\]]+\]', 'Direct use of $_REQUEST without sanitization'),
            (r'eval\s*\(', 'eval() is dangerous'),
            (r'exec\s*\(', 'exec() is dangerous'),
            (r'shell_exec\s*\(', 'shell_exec() is dangerous'),
            (r'passthru\s*\(', 'passthru() is dangerous'),
            (r'system\s*\(', 'system() is dangerous'),
            (r'\$_SERVER\[.PHP_SELF.\]', 'PHP_SELF can be XSS vulnerable'),
        ]
        
        for pattern, message in security_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                results['security_issues'].append({
                    'file': str(php_file),
                    'line': line_num,
                    'pattern': match.group(),
                    'message': message
                })
    
    def check_best_practices(self, php_file: Path, content: str, results: Dict):
        """Check for best practices violations"""
        best_practice_patterns = [
            (r'echo\s*<\?', 'Short echo tag should be used'),
            (r'<\?=', 'Short echo tag is good practice'),
            (r'error_reporting\(0\)', 'Error reporting disabled'),
            (r'ini_set\s*\(\s*[\'"]display_errors[\'"]\s*,\s*0', 'Display errors disabled'),
        ]
        
        for pattern, message in best_practice_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                results['best_practices'].append({
                    'file': str(php_file),
                    'line': line_num,
                    'pattern': match.group(),
                    'message': message
                })
    
    def print_results(self, results: Dict):
        """Print scan results"""
        total_errors = sum(len(v) for v in results.values())
        
        print("\n" + "="*60)
        print("PHP ERROR SCAN RESULTS")
        print("="*60)
        print(f"Total issues found: {total_errors}")
        print()
        
        for category, issues in results.items():
            if issues:
                print(f"\n{category.upper().replace('_', ' ')} ({len(issues)}):")
                print("-" * 60)
                for issue in issues[:10]:  # Show first 10 issues per category
                    print(f"  File: {issue['file']}")
                    if 'line' in issue:
                        print(f"  Line: {issue['line']}")
                    if 'error' in issue:
                        print(f"  Error: {issue['error']}")
                    if 'function' in issue:
                        print(f"  Function: {issue['function']}")
                    if 'missing' in issue:
                        print(f"  Missing: {issue['missing']}")
                    if 'message' in issue:
                        print(f"  Message: {issue['message']}")
                    print()
                
                if len(issues) > 10:
                    print(f"  ... and {len(issues) - 10} more")
                    print()

if __name__ == '__main__':
    scanner = PHPErrorScanner('/opt/lampp/htdocs/kewer')
    results = scanner.scan_all_php_files()
    scanner.print_results(results)
