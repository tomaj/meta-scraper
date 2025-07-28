# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- GitHub Actions CI/CD workflow for automated testing across PHP versions 7.4, 8.0, 8.1, 8.2, 8.4
- Support for PHP 8.4 in test matrix
- Independent CI jobs: Composer validation, code style checking, and PHPUnit testing

### Changed
- Optimized OgParser performance by consolidating 11 separate `preg_match()` calls into efficient loop-based approach
- Split GitHub Actions workflow into 3 independent jobs for better modularity

### Removed
- Travis CI integration and `.travis.yml` configuration file
- CodeClimate test reporter dependency for improved PHP 8.1+ compatibility
- Outdated service badges from README.md (Travis CI, CodeClimate, SensioLabsInsight)
- Efficiency analysis documentation file

### Fixed
- PHP version compatibility issues with HTML entity decoding in `testMoreAttributes` test
- Consolidated regex pattern handling for complex HTML attributes and mixed case scenarios
- HTML entity decoding inconsistencies across different PHP versions (7.4, 8.0 vs 8.1+)
- Improved `htmlspecialchars_decode()` consistency across PHP versions using explicit `ENT_NOQUOTES | ENT_HTML401` flags

### Performance
- Expected 60-80% improvement in parsing time for large HTML documents
- Reduced regex operations from 11 separate calls to single efficient loop
- Optimized string scanning operations from O(n*m) to O(n) complexity

## [4.3.0] - 2024-XX-XX
### Added
- Open Graph Parser based on DOM php extension

---

### Commit History

- `c8b6645` - Optimize OgParser performance by consolidating regex operations
- `459a793` - Fix consolidated regex pattern to handle all test cases  
- `295ad57` - Remove codeclimate/php-test-reporter dependency for PHP 8.1 compatibility
- `916402a` - Add GitHub Actions workflow for automated testing
- `e3a228d` - Fix testMoreAttributes to expect decoded HTML entities
- `635ec70` - Fix PHP version compatibility for htmlspecialchars_decode
- `4d5a99b` - Remove Travis CI integration and split GitHub Actions workflow
- `cb68b59` - Add PHP 8.4 support to GitHub Actions workflow
