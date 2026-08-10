export default function About() {
  return (
    <section id="about" className="py-20 sm:py-28 bg-dark-800">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 className="text-3xl sm:text-4xl font-display font-bold mb-2">
              About
            </h2>
            <p className="text-gold-400 font-body text-sm mb-8">
              Désir Fils — Founder, WhoIsDésir®
            </p>

            <div className="space-y-4 text-silver-400 font-body leading-relaxed">
              <p>
                Désir Fils is an AI engineer and entrepreneur building at the intersection of
                artificial intelligence, media, and hospitality. As the founder of an AI-powered
                media and hospitality company, he applies machine learning and automation across
                media production, marketing, business operations, and guest experiences.
              </p>
              <p>
                His portfolio includes <strong className="text-white">Magnitax®</strong> (AI-augmented
                tax and financial services), <strong className="text-white">Silver Parrots®</strong>
                (hospitality and concierge), the <strong className="text-white">WhoIsDésir®</strong>{' '}
                Media Agency, and the Creative Hub AI demo — an AI-powered stock valuation and
                market analysis tool that demonstrates practical AI application in finance.
              </p>
              <p>
                Désir speaks on AI in marketing, AI for small business, and practical AI adoption —
                sharing what he's learned building real products, not just theory. His talks are
                grounded in hands-on experience: training models, deploying AI tools, and using
                AI to drive measurable outcomes for real clients.
              </p>
            </div>
          </div>

          <div className="relative">
            {/* Logo placeholder */}
            <div className="aspect-[4/3] bg-dark-700 rounded-2xl border border-white/5 overflow-hidden flex items-center justify-center">
              <div className="text-center p-4">
                <p className="text-silver-500 text-sm mb-2">[LOGO — WhoIsDésir®]</p>
                <p className="text-silver-600 text-xs">Replace with brand asset from whoisdesir.com</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
