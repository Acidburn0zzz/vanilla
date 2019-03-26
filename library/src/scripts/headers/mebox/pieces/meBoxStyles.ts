/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { debugHelper, flexHelper, unit } from "@library/styles/styleHelpers";
import {styleFactory, useThemeCache} from "@library/styles/styleUtils";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { vanillaHeaderVariables } from "@library/headers/vanillaHeaderStyles";
import { layoutVariables } from "@library/layout/layoutStyles";

export const meBoxClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const formVars = formElementsVariables();
    const vanillaHeaderVars = vanillaHeaderVariables();
    const debug = debugHelper("meBox");
    const mediaQueries = layoutVariables().mediaQueries();
    const flex = flexHelper();
    const style = styleFactory("meBox");

    const root = style(
        {
            ...debug.name(),
            display: "flex",
            alignItems: "center",
            height: unit(vanillaHeaderVars.sizing.height),
        },
        mediaQueries.oneColumn({
            height: unit(vanillaHeaderVars.sizing.mobile.height),
        }),
    );

    const buttonContent = style("buttonContent", {
        ...flex.middle(),
        width: unit(formVars.sizing.height),
        maxWidth: unit(formVars.sizing.height),
        flexBasis: unit(formVars.sizing.height),
        height: unit(vanillaHeaderVars.meBox.sizing.buttonContents),
    });

    const rootFlexClass = (count: number) => {
        return style("footFlexClass",{
            flexBasis: unit(count * formElementsVariables().sizing.height),
        });
    };

    return {
        root, buttonContent, rootFlexClass };
});