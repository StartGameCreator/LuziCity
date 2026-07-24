# Luzicity - Mapa Diagramado

Atualizado em: 09/07/2026

Este arquivo usa diagramas Mermaid. Em editores compativeis, os blocos aparecem como graficos.

## 1. Mapa Geral da Plataforma

```mermaid
flowchart TD
    A["Usuario / Leitor"] --> B["Home Luzicity"]
    B --> C["Noticias editoriais"]
    B --> D["RSS importado"]
    B --> E["Radio Web"]
    B --> F["Classificados de Veiculos"]
    B --> G["Imoveis"]
    B --> H["Cidades"]
    B --> I["Redes Sociais / Loja / Comercio Local"]
    B --> J["Publicidade"]

    E --> E1["Video / Audio do locutor"]
    E --> E2["Luzicity Messenger"]
    E2 --> E3["Salas por cidade ou tema"]
    E2 --> E4["Mensagem para todos"]
    E2 --> E5["Reservado monitorado"]

    F --> F1["Carros"]
    F --> F2["Motos"]
    F --> F3["Embarcacoes Nauticas"]
    F --> F4["Anuncio com foto, video e IA"]

    G --> G1["Compra"]
    G --> G2["Venda"]
    G --> G3["Aluguel"]
    G --> G4["Anuncio com foto, video e IA"]

    J --> J1["Google Ads"]
    J --> J2["Patrocinadores locais"]
    J1 --> J3["Oculto para assinante ou patrocinador logado"]
```

## 2. Fluxo de Login e Papeis

```mermaid
flowchart TD
    A["Visitante"] --> B{"Quer entrar?"}
    B -- "Nao" --> C["Continua lendo a home"]
    B -- "Sim" --> D["Login tradicional ou social"]
    D --> E["Usuario logado"]
    E --> F{"Papel do usuario"}
    F --> G["Usuario comum"]
    F --> H["Assinante"]
    F --> I["Patrocinador"]
    F --> J["Jornalista"]
    F --> K["Colunista"]
    F --> L["Anunciante"]
    F --> M["Admin / Super Admin"]

    H --> N["Nao ve Google Ads"]
    I --> N
    J --> O["Pode atuar em conteudo conforme permissao"]
    K --> O
    L --> P["Pode anunciar"]
    M --> Q["Acessa painel administrativo"]
```

## 3. Mapa do Backend

```mermaid
flowchart LR
    A["/admin"] --> B["Saude do Sistema"]
    A --> C["Usuarios"]
    A --> D["Login Social"]
    A --> E["Links do Site"]
    A --> F["Pixels"]
    A --> G["Empresa"]
    A --> H["Conteudo"]
    A --> I["IA"]
    A --> J["Editorias"]
    A --> K["Tags"]
    A --> L["RSS"]
    A --> M["Importacao RSS"]
    A --> N["Radio"]
    A --> O["Banners"]
    A --> P["Veiculos"]
    A --> Q["Imoveis"]
    A --> R["Noticias"]

    I --> I1["ChatGPT"]
    I --> I2["Gemini"]
    I --> I3["Copilot"]

    L --> L1["Cadastrar fonte"]
    L --> L2["Testar e importar"]
    M --> M1["Editar imagem importada"]
    M --> M2["Controlar visibilidade"]
```

## 4. Fluxo RSS

```mermaid
sequenceDiagram
    participant Admin as Administrador
    participant Painel as Backend RSS
    participant Internet as Fonte RSS externa
    participant Banco as Banco SQLite
    participant Home as Home Luzicity

    Admin->>Painel: Cadastra ou atualiza fonte RSS
    Admin->>Painel: Clica em "Importar e atualizar RSS agora"
    Painel->>Internet: Busca XML RSS via HTTPS 443
    alt Fonte responde
        Internet-->>Painel: XML com noticias
        Painel->>Banco: Cria ou atualiza rss_imported_articles
        Banco-->>Home: Noticias importadas aparecem na home
    else Fonte bloqueada ou sem internet
        Internet--xPainel: Erro de conexao
        Painel-->>Admin: Mostra falha por fonte
    end
```

## 5. Fluxo de Criacao de Noticias com IA

```mermaid
flowchart TD
    A["Editor abre Backend > Noticias"] --> B["Escreve pauta ou briefing"]
    B --> C{"Escolhe IA"}
    C --> D["ChatGPT"]
    C --> E["Gemini"]
    C --> F["Copilot"]
    D --> G["Assistente gera rascunho"]
    E --> G
    F --> G
    G --> H["Editor revisa e corrige"]
    H --> I["Define categoria, tags, data e midia"]
    I --> J{"Tem video ou fotos?"}
    J -- "Sim" --> K["Entra tambem nos carrosseis"]
    J -- "Nao" --> L["Publica como noticia comum"]
    K --> M["Home"]
    L --> M
```

## 6. Fluxo da Radio e Bate-Papo

```mermaid
flowchart TD
    A["Usuario abre /radio"] --> B["Ve locutor e controles de audio/video"]
    B --> C["Escolhe apelido"]
    C --> D["Escolhe sala/regiao/tema"]
    D --> E["Entra na sala"]
    E --> F["Chat fica disponivel"]
    F --> G["Falar para todos"]
    F --> H["Marcar reservado"]
    H --> I["Escolher pessoa da sala"]
    I --> J["Monitoria pode acompanhar reservado"]
    F --> K["Enviar foto leve"]
    F --> L["Receber bip quando for marcado"]
    E --> M["Sair da sala"]
    M --> C
```

## 7. Fluxo dos Classificados de Veiculos

```mermaid
flowchart TD
    A["Usuario abre Classificados"] --> B["Escolhe tipo: Carros, Motos ou Nautica"]
    B --> C["Escolhe marca ou usa filtros"]
    C --> D["Resultados aparecem no mesmo bloco"]
    D --> E["Usuario abre anuncio"]

    F["Usuario logado"] --> G["Anunciar veiculo"]
    G --> H["Fotos pelo computador ou smartphone"]
    G --> I["Video YouTube/Facebook horizontal ou vertical"]
    G --> J["Copy com IA"]
    G --> K["Salva anuncio"]
    K --> L["Backend modera / publica"]
    L --> D
```

## 8. Fluxo dos Imoveis

```mermaid
flowchart TD
    A["Usuario abre Imoveis"] --> B["Escolhe compra, venda ou aluguel"]
    B --> C["Filtra por cidade, tipo e valor"]
    C --> D["Lista de imoveis"]
    D --> E["Detalhe do imovel"]

    F["Usuario logado"] --> G["Anunciar imovel"]
    G --> H["Fotos"]
    G --> I["Video ou iframe"]
    G --> J["Copy com IA"]
    G --> K["Salva anuncio"]
    K --> L["Backend acompanha"]
```

## 9. Mapa de Banco de Dados

```mermaid
erDiagram
    USERS ||--o{ SOCIAL_ACCOUNTS : possui
    USERS ||--o| SUBSCRIPTIONS : assina
    USERS ||--o| JOURNALIST_PROFILES : perfil
    USERS ||--o| COLUMNIST_PROFILES : perfil
    USERS ||--o| ADVERTISER_PROFILES : perfil
    USERS ||--o{ NEWS_ARTICLES : publica
    USERS ||--o{ VEHICLE_LISTINGS : anuncia
    USERS ||--o{ REAL_ESTATE_LISTINGS : anuncia

    CATEGORIES ||--o{ NEWS_ARTICLES : organiza
    CATEGORIES ||--o{ CATEGORIES : submenus
    NEWS_ARTICLES }o--o{ TAGS : marca

    RSS_FEEDS ||--o{ RSS_IMPORTED_ARTICLES : importa

    USERS {
        int id
        string name
        string email
        boolean is_active
    }

    NEWS_ARTICLES {
        int id
        string title
        string slug
        datetime published_at
        boolean is_published
    }

    RSS_FEEDS {
        int id
        string name
        string url
        boolean is_active
    }

    RSS_IMPORTED_ARTICLES {
        int id
        int rss_feed_id
        string title
        string original_url
        boolean is_visible
    }

    VEHICLE_LISTINGS {
        int id
        int user_id
        string vehicle_type
        string brand
        string model
        string status
    }

    REAL_ESTATE_LISTINGS {
        int id
        int user_id
        string purpose
        string property_type
        string city
        string status
    }
```

## 10. Mapa de Pastas

```mermaid
flowchart TD
    A["LuziCityLaravel13"] --> B["app"]
    A --> C["database"]
    A --> D["resources/views"]
    A --> E["routes"]
    A --> F["public"]
    A --> G["storage"]
    A --> H["Modules"]

    B --> B1["Controllers"]
    B --> B2["Models"]
    B --> B3["Services"]

    C --> C1["migrations"]
    C --> C2["database.sqlite"]

    D --> D1["home"]
    D --> D2["admin"]
    D --> D3["radio"]
    D --> D4["vehicles"]
    D --> D5["real-estate"]

    E --> E1["web.php"]
    E --> E2["console.php"]
```

## 11. Roadmap Recomendado

```mermaid
timeline
    title Proximos passos recomendados
    Publicacao local concluida : Laravel 13 : SQLite : Porta 9001
    Homologacao : Revisar textos : Revisar RSS : Revisar IA : Revisar anuncios
    Producao : Dominio : SSL : Hospedagem : Banco externo : Backup
    Crescimento : App/PWA : Newsletter : Assinaturas : APIs publicas
    Escala : CDN : Observabilidade : Fila : Cache avancado
```
