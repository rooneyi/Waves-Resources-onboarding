FROM node:18-alpine AS builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install --legacy-peer-deps

FROM node:18-alpine AS builder2
WORKDIR /app
COPY . .
RUN npm install --legacy-peer-deps
RUN npm run build

FROM node:18-alpine AS runner
WORKDIR /app
ENV NODE_ENV=production
COPY --from=builder /app/ .
EXPOSE 3000
CMD ["npm", "run", "start"]
