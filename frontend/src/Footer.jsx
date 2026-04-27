import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

export default function Footer() {
  const { t } = useTranslation();

  return (
    <footer style={{
      background: '#1a1a1a', padding: '24px 16px 80px', marginTop: 0,
      borderTop: '1px solid #333'
    }}>
      <div style={{ maxWidth: 600, margin: '0 auto', textAlign: 'center' }}>
        <div style={{ fontSize: 17.6, fontWeight: 700, color: '#fff', marginBottom: 4 }}>
          Kumbakonam <span style={{ color: '#C41E3A' }}>Free Matrimony</span>
        </div>
        <div style={{ fontSize: 12.1, color: '#888', marginBottom: 16, lineHeight: 1.5 }}>
          {t("footer.tagline") || "Find your perfect life partner with trust and tradition"}
        </div>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 16, flexWrap: 'wrap', marginBottom: 16 }}>
          <Link to="/about-us" style={{ fontSize: 13.2, color: '#aaa', textDecoration: 'none' }}>About Us</Link>
          <Link to="/contact" style={{ fontSize: 13.2, color: '#aaa', textDecoration: 'none' }}>Contact</Link>
          <Link to="/privacy-policy" style={{ fontSize: 13.2, color: '#aaa', textDecoration: 'none' }}>Privacy Policy</Link>
          <Link to="/terms-and-conditions" style={{ fontSize: 13.2, color: '#aaa', textDecoration: 'none' }}>Terms</Link>
        </div>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 12, marginBottom: 16 }}>
          <a href="tel:+919876543210" style={{ width: 36, height: 36, borderRadius: '50%', background: '#333', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 17.6, textDecoration: 'none' }}>📞</a>
          <a href="https://wa.me/919876543210" target="_blank" rel="noopener noreferrer" style={{ width: 36, height: 36, borderRadius: '50%', background: '#333', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 17.6, textDecoration: 'none' }}>💬</a>
        </div>

        <div style={{ fontSize: 11, color: '#555', letterSpacing: 0.5 }}>
          © {new Date().getFullYear()} Kumbakonam Free Matrimony. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
