# Global One Builders LLC — Website

The official marketing website for **Global One Builders LLC**, Indiana's premier general contractor.
Live at: **[globalonebuilders.us](https://globalonebuilders.us)**

---

## 📁 Project Structure

```
globalonebuilders/
├── index.html              ← Main HTML page (markup only)
├── CNAME                   ← Custom domain config for GitHub Pages
├── README.md               ← You are here
└── assets/
    ├── css/
    │   └── styles.css      ← All site styles
    ├── js/
    │   └── main.js         ← Site script (nav, language toggle, contact form)
    └── images/
        ├── logo.png        ← Brand logo, transparent PNG (used in header, footer, favicon)
        ├── hero-bg.jpg     ← Hero section background
        └── about.jpg       ← "About Us" section image
```

---

## ✏️ Common Edits — Where to Look

| You want to change... | Open this file |
|---|---|
| Text on the page (services, headlines, contact info) | `index.html` |
| Colors, fonts, spacing, layout | `assets/css/styles.css` |
| Language toggle, animations, form behavior | `assets/js/main.js` |
| The logo | Replace `assets/images/logo.png` |
| Hero background photo | Replace `assets/images/hero-bg.jpg` |
| About-section photo | Replace `assets/images/about.jpg` |
| Domain (e.g. moving off `globalonebuilders.us`) | `CNAME` |

---

## 🌐 Bilingual Content (ES / EN)

Every translatable element on the page carries two attributes:

```html
<span data-en="English text" data-es="Spanish text">Spanish text</span>
```

To add a new bilingual element, follow the same pattern. The Spanish text shows by default; the `setLang()` function in `assets/js/main.js` swaps `innerHTML` based on the active language.

---

## 📬 Contact Form

The "Request a Free Estimate" form posts to **[Web3Forms](https://web3forms.com)** — no server required.

- The access key is in `assets/js/main.js` (`WEB3FORMS_KEY`).
- Submissions are delivered to the email associated with that key.
- To change the destination email, log in at web3forms.com and rotate the key.

---

## 🚀 Deployment

The site is hosted on **GitHub Pages** with a custom domain (see `CNAME`).
Push to the `main` branch → GitHub Pages rebuilds automatically.

---

## 📞 Contact

- **Phone:** +1 574-386-8817
- **Email:** edgar@globalonebuilders.us
- **Service Area:** Indiana, Wisconsin, Ohio & surrounding regions
