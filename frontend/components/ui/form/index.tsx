import React from 'react'
import { Controller, FormProvider } from 'react-hook-form'

export function Form({ children, ...form }: any) {
  // If form methods are passed (useForm result), provide them via FormProvider
  if (Object.keys(form).length) {
    return <FormProvider {...form}>{children}</FormProvider>
  }
  return <>{children}</>
}

export function FormControl({ children, className = '' }: any) {
  return <div className={className}>{children}</div>
}

export function FormItem({ children, className = '' }: any) {
  return <div className={className}>{children}</div>
}

export function FormLabel({ children, htmlFor, className = '' }: any) {
  return (
    <label htmlFor={htmlFor} className={`block text-sm font-medium text-slate-700 ${className}`}>
      {children}
    </label>
  )
}

export function FormMessage({ children, className = '' }: any) {
  if (!children) return null
  return <p className={`text-sm text-red-600 ${className}`}>{children}</p>
}

type FormFieldRender = (args: { field: any; error?: any }) => React.ReactNode

export function FormField({ name, render, control, ...rest }: { name: string; render: FormFieldRender; control?: any; [key: string]: any }) {
  // If a react-hook-form control is provided, use Controller to bind the input
  if (control) {
    return (
      <Controller
        control={control}
        name={name}
        render={({ field, fieldState }) => render({ field, error: fieldState.error })}
      />
    )
  }

  // Fallback: minimal fake `field` object so inputs can mount without runtime errors.
  const field = {
    name,
    value: undefined,
    onChange: () => {},
    onBlur: () => {},
    ref: () => {},
  }

  return render({ field })
}

export default Form
