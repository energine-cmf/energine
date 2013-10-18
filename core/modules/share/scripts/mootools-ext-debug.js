/**
 * @file Additional extensions to the MooTools framework.
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0.0
 */

/**
 * Mutator that creates static members for a class.
 *
 * @augments Class.Mutators
 *
 * @constructor
 * @param {Object} members Object that contains properties and methods, which must be static in the class.
 */
Class.Mutators.Static = function (members) {
    this.extend(members);
};