import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

export default function Footer() {
  const { t } = useTranslation();

  return (
    <footer style={{
      background: '#1A1A2E', padding: '28px 16px 88px', marginTop: 0,
      borderTop: '2px solid #0D7B6A'
    }}>
      <div style={{ maxWidth: 600, margin: '0 auto', textAlign: 'center' }}>
        <div style={{ fontSize: 20, fontWeight: 700, color: '#fff', marginBottom: 4 }}>
          Chennai <span style={{ color: '#C9A84C' }}>Profile Matrimony</span>
        </div>
        <div style={{ fontSize: 15, color: '#A8D9D0', marginBottom: 18, lineHeight: 1.6 }}>
          {t("footer.tagline") || "Find your perfect life partner with trust and tradition"}
        </div>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 20, flexWrap: 'wrap', marginBottom: 18 }}>
          <Link to="/about-us" style={{ fontSize: 16, color: '#C8EDE6', textDecoration: 'none' }}>About Us</Link>
          <Link to="/contact" style={{ fontSize: 16, color: '#C8EDE6', textDecoration: 'none' }}>Contact</Link>
          <Link to="/privacy-policy" style={{ fontSize: 16, color: '#C8EDE6', textDecoration: 'none' }}>Privacy Policy</Link>
          <Link to="/terms-and-conditions" style={{ fontSize: 16, color: '#C8EDE6', textDecoration: 'none' }}>Terms</Link>
        </div>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 14, marginBottom: 18 }}>
          <a href="tel:+919876543210" style={{ width: 42, height: 42, borderRadius: '50%', background: '#0D7B6A', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, textDecoration: 'none' }}>📞</a>
          <a href="https://wa.me/919876543210" target="_blank" rel="noopener noreferrer" style={{ width: 42, height: 42, borderRadius: '50%', background: '#6B3FA0', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, textDecoration: 'none' }}>💬</a>
        </div>

        <div style={{ fontSize: 14, color: '#A8D9D0', letterSpacing: 0.4 }}>
          © {new Date().getFullYear()} Chennai Profile Matrimony. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
