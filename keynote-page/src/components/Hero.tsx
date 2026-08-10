export default function Hero() {
  return (
    <header className="relative min-h-screen flex items-center justify-center overflow-hidden">
      <div className="absolute inset-0 bg-gradient-to-b from-dark-900 via-dark-800 to-dark-900" />
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold-500/10 via-transparent to-transparent" />

      <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 text-center py-32">
        <div className="mb-8">
          {/* Headshot placeholder */}
          <div className="w-28 h-28 sm:w-36 sm:h-36 mx-auto rounded-full bg-dark-600 border-2 border-gold-400/30 overflow-hidden aspect-square">
            <div className="w-full h-full flex items-center justify-center text-silver-500 text-xs text-center p-2">
              [HEADSHOT]
            </div>
          </div>
        </div>

        <h1 className="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-display font-bold leading-tight mb-6">
          Désir Fils
        </h1>

        <p className="text-lg sm:text-xl md:text-2xl text-silver-400 font-body font-light mb-4">
          AI Engineer, Entrepreneur & Keynote Speaker
        </p>

        <p className="text-base sm:text-lg text-silver-500 font-body max-w-2xl mx-auto mb-10">
          Helping small businesses and entrepreneurs navigate AI adoption — from marketing and media production to practical, real-world implementation.
        </p>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
          <a
            href="mailto:vroland@northmiamifl.gov?subject=Speaker%20Inquiry%20%E2%80%93%20D%C3%A9sir%20Fils&body=Hi%20Vroland%2C%0A%0AI%27m%20D%C3%A9sir%20Fils%2C%20an%20AI%20engineer%20and%20entrepreneur%20in%20Miami.%20I%20came%20across%20the%20AI%20%26%20Marketing%20Summit%20(Thursday%2C%20August%206%2C%202026%2C%2010%20AM%E2%80%932%20PM%2C%20Joe%20Celestin%20Center%2C%20Miami%2C%20FL)%20and%20would%20love%20to%20attend%20and%20explore%20speaking.%0A%0AI%20build%20AI%20tools%20for%20media%2C%20marketing%2C%20and%20small%20business%20operations%20%E2%80%94%20founder%20of%20Magnitax%C2%AE%20and%20the%20WhoIsD%C3%A9sir%C2%AE%20Media%20Agency.%20My%20headline%20talk%20features%20the%20Creative%20Hub%20AI%20demo.%0A%0AIf%20this%20year%27s%20lineup%20is%20set%2C%20I%27d%20welcome%20consideration%20for%20a%20future%20event.%0A%0ABest%2C%0AD%C3%A9sir%20Fils"
            className="inline-flex items-center px-8 py-4 text-base font-medium text-dark-900 bg-gold-400 hover:bg-gold-500 rounded-lg transition-all duration-200 min-h-[52px] shadow-lg shadow-gold-500/20"
          >
            Inquire About Speaking
          </a>
          <a
            href="#past-talks"
            className="inline-flex items-center px-8 py-4 text-base font-medium text-silver-400 border border-white/10 hover:border-gold-400/50 rounded-lg transition-all duration-200 min-h-[52px]"
          >
            Watch Past Talks
          </a>
        </div>
      </div>
    </header>
  )
}
