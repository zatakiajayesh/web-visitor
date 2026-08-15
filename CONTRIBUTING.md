# Contributing Guide

Thank you for your interest in contributing to Web Visitor Tracker! 

## Code of Conduct

Be respectful and professional. We value all contributions and want to maintain a welcoming community.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/web-visitor.git`
3. Create a feature branch: `git checkout -b feature/your-feature`
4. Make changes and commit: `git commit -am 'Add feature'`
5. Push to branch: `git push origin feature/your-feature`
6. Create a Pull Request

## Development Setup

```bash
# Clone repository
git clone https://github.com/zatakiajayesh/web-visitor.git
cd web-visitor

# Setup environment
cp .env.example .env
# Edit .env with your settings

# Setup database
php database/setup.php

# Run tests (when available)
./vendor/bin/phpunit
```

## Coding Standards

### PHP Code Style

- Follow PSR-12 coding standard
- Use camelCase for method/variable names
- Use PascalCase for class names
- Add docblocks to all classes and methods
- Use type hints where possible

Example:
```php
/**
 * Get visitor by ID
 * 
 * @param int $id The visitor ID
 * @return array|false Visitor data or false if not found
 */
public function getVisitor(int $id) {
    // Implementation
}
```

### JavaScript Code Style

- Use 'use strict' at the top of files
- Use const/let, avoid var
- Use meaningful variable names
- Add comments for complex logic
- Use arrow functions where appropriate

Example:
```javascript
/**
 * Track page visit
 */
function trackPageVisit() {
    const data = {
        page_url: window.location.href,
        referrer: document.referrer
    };
    
    return fetch(TRACKER_URL, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}
```

### CSS Code Style

- Use kebab-case for class names
- Group related styles together
- Use variables for colors and common values
- Mobile-first responsive design
- BEM naming convention for complex components

Example:
```css
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.card__title {
    font-weight: 600;
    margin-bottom: 10px;
}

.card__title--primary {
    color: #667eea;
}
```

## Commit Messages

Use clear, descriptive commit messages:

```
Add: New feature description
Fix: Bug fix description
Update: Update documentation or code
Refactor: Refactor existing code
Remove: Remove deprecated code
```

Example:
```
Add: Visitor geolocation tracking
Fix: Session timeout calculation error
Update: API documentation for new endpoints
```

## Pull Request Process

1. Ensure your code follows coding standards
2. Update documentation if needed
3. Add tests for new features
4. Provide clear description of changes
5. Link related issues if applicable
6. Wait for review and address feedback

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Related Issues
Closes #123

## Testing
Describe how you tested changes

## Checklist
- [ ] Code follows style guidelines
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] No new warnings generated
```

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/VisitorTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Writing Tests

```php
<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class VisitorTest extends TestCase {
    /**
     * Test visitor tracking
     */
    public function testTrackVisitor() {
        $visitor = new Visitor();
        $result = $visitor->trackVisitor('192.168.1.1', 'Mozilla/5.0', '/test');
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }
}
```

## Documentation

### README Contributions

- Keep it concise and clear
- Use examples where helpful
- Update table of contents
- Include relevant links

### API Documentation

Document new endpoints:

```markdown
## Endpoint Name

### Request
```
POST /api/endpoint
Content-Type: application/json

{
  "param": "value"
}
```

### Response
```json
{
  "success": true,
  "data": {}
}
```
```

## Reporting Bugs

Create an issue with:

1. **Description**: Clear description of the bug
2. **Steps to Reproduce**: Detailed steps
3. **Expected Behavior**: What should happen
4. **Actual Behavior**: What actually happens
5. **Environment**: PHP version, OS, browser, etc.
6. **Screenshots**: If applicable

### Bug Report Template

```markdown
## Description
Brief description

## Steps to Reproduce
1. ...
2. ...
3. ...

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- OS: 
- PHP Version: 
- Browser: 
- Database: 

## Screenshots
If applicable
```

## Feature Requests

When proposing new features:

1. **Description**: Clear description of feature
2. **Use Case**: Why is this needed?
3. **Proposed Solution**: How should it work?
4. **Alternatives**: Alternative approaches considered

### Feature Request Template

```markdown
## Description
Brief description of feature

## Problem
What problem does this solve?

## Proposed Solution
How should this work?

## Alternatives
Other approaches considered

## Additional Context
Any other relevant information
```

## Review Process

1. Maintainers will review your contribution
2. They may request changes or ask questions
3. Address feedback and re-submit
4. Once approved, your contribution will be merged

## Maintainers

- [@zatakiajayesh](https://github.com/zatakiajayesh) - Lead Maintainer

## Community

- GitHub Issues: Bug reports and feature requests
- GitHub Discussions: General questions and discussions
- Email: Check repository for contact information

## Recognition

Contributors will be recognized in:
- CONTRIBUTORS.md file
- Release notes for major contributions
- GitHub contributors page

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

Feel free to open an issue or discussion with any questions. We're here to help!
