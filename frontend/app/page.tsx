import Link from 'next/link'
import { ArrowRight, Layers, Layout, ShieldCheck, Sparkles, User, LogIn, UserPlus } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

export default function Home() {
  return (
    <div className="min-h-screen bg-background text-foreground flex flex-col justify-between">
      
      {/* 1. HEADER / NAVBAR */}


      {/* 2. HERO SECTION */}
      <main className="flex-1">
        <section className="relative py-20 md:py-28 overflow-hidden">
          {/* Effet de fond lumineux */}
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[300px] bg-primary/10 blur-[120px] rounded-full pointer-events-none -z-10" />

          <div className="page-container max-w-4xl mx-auto text-center px-4 space-y-6">
            <Badge variant="secondary" className="px-3 py-1 text-xs sm:text-sm font-medium gap-1.5 rounded-full">
              Projet Frontend Next.js & shadcn/ui
            </Badge>

            <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight leading-tight font-serif text-primary">
              Gérez vos ressources frontend <br className="hidden sm:inline" />
              <span className="bg-gradient-to-r from-primary via-indigo-500 to-purple-600 bg-clip-text text-transparent">
                avec élégance et rapidité.
              </span>
            </h1>

            <p className="max-w-2xl mx-auto text-lg text-muted-foreground sm:text-xl font-normal leading-relaxed">
              Waves Resources regroupe l'ensemble de vos outils, composants et accès utilisateurs au même endroit dans un écosystème moderne.
            </p>

            {/* Actions CTA */}
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-6">
              <Button asChild size="lg" className="w-full sm:w-auto h-14 px-10 text-base gap-3 cta-primary">
                <Link href="/register" className="flex items-center gap-3">
                  Commencer gratuitement
                  <ArrowRight className="w-5 h-5" />
                </Link>
              </Button>

              <Button asChild variant="outline" size="lg" className="w-full sm:w-auto h-14 px-8 text-base gap-2 border-white/20 bg-white/5">
                <Link href="/me" className="flex items-center gap-2">
                  <User className="w-4 h-4 text-white/90" />
                  Accéder à mon profil
                </Link>
              </Button>
            </div>
          </div>
        </section>

        {/* 3. SECTION FONCTIONNALITÉS / STRUCTURE */}
        <section className="py-12 bg-muted/40 border-y">
          <div className="container max-w-5xl mx-auto px-4">
            <div className="grid md:grid-cols-3 gap-6">
              
              <Card className="bg-background shadow-sm border-muted">
                <CardHeader>
                  <div className="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-2">
                    <Layout className="w-5 h-5" />
                  </div>
                  <CardTitle className="text-lg">Design Atomique</CardTitle>
                  <CardDescription>
                    Construit avec Radix UI et Tailwind CSS pour une flexibilité totale et un style épuré.
                  </CardDescription>
                </CardHeader>
              </Card>

              <Card className="bg-background shadow-sm border-muted">
                <CardHeader>
                  <div className="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-2">
                    <ShieldCheck className="w-5 h-5" />
                  </div>
                  <CardTitle className="text-lg">Authentification</CardTitle>

                  <CardDescription>
                    Espaces dédiés pour la gestion des comptes, la connexion sécurisée et les profils.
                  </CardDescription>
                </CardHeader>
              </Card>

              <Card className="bg-background shadow-sm border-muted">
                <CardHeader>
                  <div className="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-2">
                    <Sparkles className="w-5 h-5" />
                  </div>
                  <CardTitle className="text-lg">Prêt pour la prod</CardTitle>
                  <CardDescription>
                    Performances optimisées avec Next.js App Router et chargement instantané.
                  </CardDescription>
                </CardHeader>
              </Card>

            </div>
          </div>
        </section>
      </main>

      {/* 4. FOOTER */}
      <footer className="border-t py-6 md:py-8">
        <div className="container max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 px-4 text-sm text-muted-foreground">
          <p>© {new Date().getFullYear()} Waves Resources. Tous droits réservés.</p>
          <div className="flex gap-4">
            <Link href="/login" className="hover:underline">Connexion</Link>
            <Link href="/register" className="hover:underline">S'inscrire</Link>
            <Link href="/me" className="hover:underline">Profil</Link>
          </div>
        </div>
      </footer>

    </div>
  )
}