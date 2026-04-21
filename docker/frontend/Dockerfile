FROM node:22-alpine

ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL

WORKDIR /app

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ ./
RUN npm run build

# Copiar el build al volumen compartido con Nginx
CMD ["sh", "-c", "cp -r dist/. /output/"]
