export default function Footer() {
  return (
    <footer className="py-12 border-t border-white/5 bg-dark-900">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="text-center md:text-left">
            <p className="text-sm font-display font-semibold text-white">
              Désir Fils
            </p>
            <p className="text-xs text-silver-600 mt-1">
              AI Engineer, Entrepreneur & Keynote Speaker
            </p>
          </div>

          <div className="flex items-center gap-6">
            <a
              href="https://whoisdesir.com"
              target="_blank"
              rel="noopener noreferrer"
              className="text-xs text-silver-500 hover:text-gold-400 transition-colors"
            >
              whoisdesir.com
            </a>
            <a
              href="https://whoisdesir.com/about"
              target="_blank"
              rel="noopener noreferrer"
              className="text-xs text-silver-500 hover:text-gold-400 transition-colors"
            >
              About
            </a>
            <a
              href="mailto:vroland@northmiamifl.gov"
              className="text-xs text-silver-500 hover:text-gold-400 transition-colors"
            >
              Contact
            </a>
          </div>

          <p className="text-[10px] text-silver-700">
            &copy; {new Date().getFullYear()} Désir Fils. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  )
}
