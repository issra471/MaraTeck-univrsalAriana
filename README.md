# MaraTeck-univrsalAriana

An accessible, WCAG AA-compliant dashboard system with comprehensive keyboard navigation and screen reader support.

## 🌟 Features

### Accessibility-First Design
- **WCAG AA Compliant**: Meets Web Content Accessibility Guidelines 2.1 Level AA
- **Keyboard Navigation**: Full keyboard support with visible focus indicators
- **Screen Reader Friendly**: Semantic HTML, ARIA labels, and live regions
- **High Contrast Mode**: Built-in high contrast toggle for better visibility
- **Reduced Motion**: Respects `prefers-reduced-motion` user preferences
- **Large Hit Targets**: All interactive elements meet 44×44px minimum size

### Dashboard Features
- Real-time analytics and statistics
- Interactive charts with tabular data alternatives
- Responsive grid layout
- System status monitoring
- Transaction history tables
- Keyboard-navigable data tables

## 🚀 Quick Start

1. **Open the dashboard**:
   - Open `index.html` in your web browser
   - No build process required - pure HTML, CSS, and JavaScript

2. **Test accessibility features**:
   - Press `Tab` to navigate through interactive elements
   - Press `Shift+Tab` to navigate backwards
   - Use arrow keys to navigate within tables and menus
   - Try the "Show table view" buttons to see chart data alternatives

## 📁 Project Structure

```
MaraTeck-univrsalAriana/
├── index.html                          # Main dashboard page
├── styles/
│   └── accessibility-dashboard.css     # Accessible styles (WCAG compliant)
├── scripts/
│   └── accessibility-dashboard.js      # Accessibility helpers
├── docs/
│   └── INTEGRATION.md                  # Integration guide
└── README.md                           # This file
```

## 🎨 Accessibility Features

### Skip Links
- Skip to main content link appears on keyboard focus
- Allows keyboard users to bypass navigation

### Semantic HTML
- Proper use of landmarks (`header`, `nav`, `main`, `aside`, `footer`)
- Semantic table markup with `<caption>`, `scope` attributes
- Heading hierarchy for proper document outline

### Keyboard Navigation
- **Tab/Shift+Tab**: Navigate interactive elements
- **Arrow Keys**: Navigate within tables and menus
- **Enter/Space**: Activate buttons and links
- **Escape**: Close modals and dialogs

### Screen Reader Support
- ARIA labels for all interactive elements
- Live regions for dynamic content announcements
- Descriptive chart summaries
- Table captions and proper headers

### Visual Accessibility
- High contrast mode toggle
- Focus indicators with 3px amber outline
- Large text (minimum 16px base)
- Color-independent information (not relying on color alone)
- Responsive text sizing with `clamp()`

### Color Contrast
All text meets WCAG AA standards:
- Regular text: 4.5:1 contrast ratio minimum
- Large text: 3:1 contrast ratio minimum
- Interactive elements: Clear visual states

## 📖 Integration Guide

See [docs/INTEGRATION.md](docs/INTEGRATION.md) for detailed instructions on:
- Adding accessibility features to existing pages
- Using skip links and landmarks
- Implementing chart table toggles
- Common dashboard fixes

## 🎯 Keyboard Testing Checklist

- [ ] Skip link appears on first Tab press
- [ ] All interactive elements are reachable via Tab
- [ ] Focus indicator is visible on all focusable elements
- [ ] Arrow keys work in tables and menus
- [ ] Chart table toggles work with Enter/Space
- [ ] High contrast toggle works
- [ ] Modal focus trap works (if modals present)
- [ ] No keyboard traps (can navigate away from all elements)

## 🔧 Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)

Tested with:
- NVDA screen reader (Windows)
- JAWS screen reader (Windows)
- VoiceOver (macOS/iOS)
- TalkBack (Android)

## 📊 Dashboard Components

### Stats Cards
- Key metrics display
- Color-coded change indicators
- Screen reader friendly value announcements

### Charts
- Visual charts with text descriptions
- Toggle-able table view alternatives
- Keyboard accessible

### Data Tables
- Fully keyboard navigable
- Arrow key navigation between cells
- Hover and focus-within states
- Screen reader optimized with proper headers

### Filters & Actions
- Accessible form controls
- Clear labels for all inputs
- Minimum 44×44px hit targets

## 🎓 Testing

### Automated Testing
Run accessibility checks using:
- WAVE browser extension
- axe DevTools
- Lighthouse accessibility audit

### Manual Testing
1. **Keyboard only**: Unplug/disable mouse, navigate entire page
2. **Screen reader**: Enable NVDA/JAWS/VoiceOver, verify all content is announced
3. **Zoom**: Test at 200% zoom level
4. **Color blindness**: Use color blindness simulators
5. **High contrast**: Test Windows High Contrast Mode

## 🐛 Common Issues & Solutions

### Charts not rendering
- Ensure data is loaded before chart initialization
- Add null/undefined checks
- Provide error fallback messages

### Keyboard navigation not working
- Check for `tabindex="-1"` preventing focus
- Verify JavaScript event listeners are attached
- Ensure elements are not hidden with `display: none`

### Screen reader issues
- Verify ARIA attributes are correct
- Check for proper heading hierarchy
- Ensure live regions are present

## 📝 License

Copyright © 2025 MaraTeck Universal Ariana. All rights reserved.

## 🤝 Contributing

When contributing, ensure:
1. All new features are keyboard accessible
2. WCAG AA compliance is maintained
3. Screen reader testing is performed
4. Documentation is updated

## 📧 Support

For issues or questions, please open an issue in the repository.