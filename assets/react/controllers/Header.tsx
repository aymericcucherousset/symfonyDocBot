import React, { ChangeEvent } from "react";

interface Props {
    readonly homeLabel: string,
    readonly homePath: string,
    readonly language: string,
    readonly languagesSupported: string[],
}

export default function Header({ homeLabel, homePath, language, languagesSupported = ['en']}: Props) {
    
    const handleChangeLanguage = (e: ChangeEvent<HTMLSelectElement>) => {
        window.location.replace(`/${e.target.value.toLocaleLowerCase()}`)
    }
    
    return <header className="container">
        <nav>
            <ul>
                <li>
                    <a href={ homePath }>{ homeLabel }</a>
                </li>
            </ul>
            <ul>
                <li>
                    <select id="local-selector" onChange={e => handleChangeLanguage(e)} defaultValue={language.toLocaleLowerCase()}>
                        {
                            languagesSupported.map(languageSupported =>
                                <option key={languageSupported} value={languageSupported.toLocaleLowerCase()}>
                                    { languageSupported.toLocaleUpperCase() }
                                </option>
                            )
                        }
                    </select>
                </li>
            </ul>
        </nav>
    </header>
}
