"use client"

import Link from 'next/link'
import { ArrowRight, User, LogIn, UserPlus, Sparkles } from 'lucide-react'
import { Button } from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import CardHeader from '@/components/ui/CardHeader'
import CardContent from '@/components/ui/CardContent'
import CardTitle from '@/components/ui/CardTitle'
import CardDescription from '@/components/ui/CardDescription'
import Badge from '@/components/ui/Badge'

export default function Home() {
  return (
    <main className="min-h-screen w-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-4">
      {/* Container principal avec effet de halo subtil */}
      <div className="relative w-full max-w-lg">
        <div className="absolute -top-12 -left-12 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none" />
        
        <Card className="shadow-xl border-slate-200/80 dark:border-slate-800 backdrop-blur-sm">
          <CardHeader className="space-y-3 pb-4">
            <div className="flex items-center justify-between">
              <Badge variant="outline" className="gap-1 px-2.5 py-0.5 text-xs font-medium">
                <Sparkles className="w-3 h-3 text-primary" />
                Next.js + shadcn/ui
              </Badge>
            </div>
            <CardTitle className="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">
              Waves Resources
            </CardTitle>
            <CardDescription className="text-base text-muted-foreground leading-relaxed">
              Plateforme moderne pour explorer et gérer vos ressources frontend en toute simplicité.
            </CardDescription>
          </CardHeader>

          <CardContent className="pt-2">
            <div className="grid gap-3 sm:grid-cols-2">
              {/* Action Principale - Call to Action */}
              <Button asChild size="lg" className="sm:col-span-2 gap-2 shadow-sm font-semibold">
                <Link href="/register">
                  <UserPlus className="w-4 h-4" />
                  Créer un compte
                  <ArrowRight className="w-4 h-4 ml-auto opacity-70" />
                </Link>
              </Button>

              {/* Actions Secondaires */}
              <Button asChild variant="outline" size="lg" className="gap-2">
                <Link href="/login">
                  <LogIn className="w-4 h-4 text-muted-foreground" />
                  Connexion
                </Link>
              </Button>

              <Button asChild variant="secondary" size="lg" className="gap-2">
                <Link href="/me">
                  <User className="w-4 h-4 text-muted-foreground" />
                  Mon Profil
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </main>
  )
}