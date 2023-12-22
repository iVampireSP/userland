{{/*
Expand the name of the chart.
*/}}
{{- define ".chart.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define ".chart.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define ".chart.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define ".chart.labels" -}}
helm.sh/chart: {{ include ".chart.chart" . }}
{{ include ".chart.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define ".chart.selectorLabels" -}}
app.kubernetes.io/name: {{ include ".chart.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define ".chart.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include ".chart.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}



{{- define "_container_env" -}}
envFrom:
    - configMapRef:
        name: {{ include ".chart.fullname" . }}
env:
    - name: APP_KEY
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: APP_KEY
    - name: DB_PASSWORD
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: DB_PASSWORD
    - name: REDIS_PASSWORD
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: REDIS_PASSWORD
    - name: JWT_PRIVATE_KEY
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: JWT_PRIVATE_KEY
    - name: JWT_PUBLIC_KEY
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: JWT_PUBLIC_KEY
    - name: MAIL_PASSWORD
      valueFrom:
        secretKeyRef:
          name: {{ include ".chart.fullname" . }}
          key: MAIL_PASSWORD
{{- end -}}