import React, { useEffect, lazy, Suspense } from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom'
import Navbar from './Navbar.jsx'
import Home from './home.jsx'                  // eager: first paint route
import MobileGate from './MobileGate.jsx'      // eager: wraps Home
import './index.css'
import './i18n.js'
import { initAnalytics, trackPageview } from './analytics.js'

initAnalytics()

// Lazy routes — split into their own chunks. Mobile users on Instagram webview
// no longer pay the cost of loading the whole app up front.
const Contact            = lazy(() => import('./ContactUs.jsx'))
const Registration       = lazy(() => import('./Registration.jsx'))
const Detail             = lazy(() => import('./Detail.jsx'))
const PrivacyPolicy      = lazy(() => import('./PrivacyPolicy.jsx'))
const AboutUs            = lazy(() => import('./AboutUs.jsx'))
const TermsAndConditions = lazy(() => import('./TermsAndConditions.jsx'))
const Search             = lazy(() => import('./search.jsx'))
const GoogleForm         = lazy(() => import('./GoogleForm.jsx'))
const GoogleFormTA       = lazy(() => import('./GoogleFormTA.jsx'))

function ScrollToTop() {
  const { pathname, search } = useLocation();
  useEffect(() => { window.scrollTo(0, 0); }, [pathname]);
  // Fire a GA pageview on every SPA route change. No-op when GA is unconfigured.
  useEffect(() => { trackPageview(pathname + search); }, [pathname, search]);
  return null;
}

// Tiny inline fallback — no extra network request, brand-colored, instant.
const RouteFallback = () => (
  <div style={{
    minHeight: '60vh', display: 'flex', alignItems: 'center', justifyContent: 'center'
  }}>
    <div style={{
      width: 32, height: 32, border: '3px solid #f0e0e0',
      borderTopColor: '#8B0000', borderRadius: '50%',
      animation: 'spin 0.8s linear infinite',
    }} />
    <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
  </div>
);

function App() {
  return (
    <div>
      <ScrollToTop />
      <Navbar />
      <Suspense fallback={<RouteFallback />}>
        <Routes>
          <Route path="/" element={<MobileGate><Home /></MobileGate>} />
          <Route path="/registration" element={<Registration />} />
          <Route path="/google-form" element={<GoogleForm />} />
          <Route path="/google-form-ta" element={<GoogleFormTA />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/detail/:id" element={<Detail />} />
          <Route path="/privacy-policy" element={<PrivacyPolicy />} />
          <Route path="/about-us" element={<AboutUs />} />
          <Route path="/terms-and-conditions" element={<TermsAndConditions />} />
          <Route path="/search" element={<Search />} />
        </Routes>
      </Suspense>
    </div>
  )
}

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <Router>
      <App />
    </Router>
  </React.StrictMode>,
)
